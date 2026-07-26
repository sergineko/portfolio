<?php
declare(strict_types=1);

if (!defined('PORTFOLIO_APP') && PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

/**
 * Envía un correo usando el transporte local de PHP y, si no está disponible,
 * entrega directamente al servidor MX del dominio destinatario.
 */
function portfolioSendEmail(
    string $recipient,
    string $fromEmail,
    string $replyTo,
    string $subject,
    string $body
): bool {
    $headers = [
        "From: Portfolio Sergio <{$fromEmail}>",
        "Reply-To: {$replyTo}",
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: 8bit',
        'X-Mailer: PHP/' . PHP_VERSION,
    ];

    if (
        function_exists('mail') &&
        @mail($recipient, $subject, $body, implode("\r\n", $headers))
    ) {
        return true;
    }

    return portfolioSendViaMx(
        $recipient,
        $fromEmail,
        $replyTo,
        $subject,
        $body
    );
}

/**
 * @param list<string>|null $mxHosts Permite inyectar hosts únicamente en pruebas.
 */
function portfolioSendViaMx(
    string $recipient,
    string $fromEmail,
    string $replyTo,
    string $subject,
    string $body,
    ?array $mxHosts = null,
    int $port = 25,
    bool $allowStartTls = true
): bool {
    $recipientDomain = portfolioEmailDomain($recipient);
    $fromDomain = portfolioEmailDomain($fromEmail);

    if ($recipientDomain === '' || $fromDomain === '') {
        return false;
    }

    $hosts = $mxHosts ?? portfolioResolveMxHosts($recipientDomain);
    $hosts = array_slice(array_values(array_unique($hosts)), 0, 3);

    foreach ($hosts as $host) {
        $host = rtrim(strtolower(trim($host)), '.');

        if ($host === '') {
            continue;
        }

        $socket = null;

        try {
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                    'peer_name' => $host,
                    'SNI_enabled' => true,
                ],
            ]);

            $errorCode = 0;
            $errorMessage = '';
            $socket = @stream_socket_client(
                "tcp://{$host}:{$port}",
                $errorCode,
                $errorMessage,
                6,
                STREAM_CLIENT_CONNECT,
                $context
            );

            if (!is_resource($socket)) {
                throw new RuntimeException("Connection failed with code {$errorCode}.");
            }

            stream_set_blocking($socket, true);
            stream_set_timeout($socket, 8);

            [$greetingCode] = portfolioReadSmtpResponse($socket);
            portfolioRequireSmtpCode($greetingCode, [220]);

            portfolioWriteSmtpCommand($socket, "EHLO {$fromDomain}");
            [$ehloCode, $ehloResponse] = portfolioReadSmtpResponse($socket);

            if ($ehloCode !== 250) {
                portfolioWriteSmtpCommand($socket, "HELO {$fromDomain}");
                [$heloCode] = portfolioReadSmtpResponse($socket);
                portfolioRequireSmtpCode($heloCode, [250]);
                $ehloResponse = '';
            }

            if (
                $allowStartTls &&
                extension_loaded('openssl') &&
                preg_match('/^250[ -]STARTTLS(?:\s|$)/mi', $ehloResponse) === 1
            ) {
                portfolioSmtpCommand($socket, 'STARTTLS', [220]);

                if (
                    stream_socket_enable_crypto(
                        $socket,
                        true,
                        STREAM_CRYPTO_METHOD_TLS_CLIENT
                    ) !== true
                ) {
                    throw new RuntimeException('TLS negotiation failed.');
                }

                portfolioSmtpCommand($socket, "EHLO {$fromDomain}", [250]);
            }

            portfolioSmtpCommand($socket, "MAIL FROM:<{$fromEmail}>", [250]);
            portfolioSmtpCommand($socket, "RCPT TO:<{$recipient}>", [250, 251]);
            portfolioSmtpCommand($socket, 'DATA', [354]);

            $message = portfolioBuildSmtpMessage(
                $recipient,
                $fromEmail,
                $replyTo,
                $subject,
                $body,
                $fromDomain
            );

            portfolioWriteAll($socket, $message . "\r\n.\r\n");
            [$acceptedCode] = portfolioReadSmtpResponse($socket);
            portfolioRequireSmtpCode($acceptedCode, [250]);

            portfolioWriteSmtpCommand($socket, 'QUIT');
            fclose($socket);

            return true;
        } catch (Throwable $exception) {
            $reason = preg_replace('/[\r\n]+/', ' ', $exception->getMessage())
                ?? 'Unknown transport error.';
            error_log(
                "Portfolio contact form: MX delivery failed for {$host}: {$reason}"
            );

            if (is_resource($socket)) {
                fclose($socket);
            }
        }
    }

    return false;
}

/**
 * @return list<string>
 */
function portfolioResolveMxHosts(string $domain): array
{
    $entries = [];
    $hosts = [];
    $weights = [];

    if (function_exists('getmxrr') && getmxrr($domain, $hosts, $weights)) {
        foreach ($hosts as $index => $host) {
            $entries[] = [
                'host' => (string) $host,
                'priority' => (int) ($weights[$index] ?? 0),
            ];
        }
    } elseif (function_exists('dns_get_record')) {
        $records = dns_get_record($domain, DNS_MX);

        if (is_array($records)) {
            foreach ($records as $record) {
                if (!isset($record['target'])) {
                    continue;
                }

                $entries[] = [
                    'host' => (string) $record['target'],
                    'priority' => (int) ($record['pri'] ?? 0),
                ];
            }
        }
    }

    if ($entries === []) {
        return [$domain];
    }

    usort(
        $entries,
        static fn (array $left, array $right): int =>
            $left['priority'] <=> $right['priority']
    );

    $resolved = [];

    foreach ($entries as $entry) {
        $host = rtrim(strtolower(trim($entry['host'])), '.');

        if ($host !== '') {
            $resolved[] = $host;
        }
    }

    return array_values(array_unique($resolved));
}

function portfolioEmailDomain(string $email): string
{
    $separator = strrpos($email, '@');

    if ($separator === false) {
        return '';
    }

    return rtrim(strtolower(substr($email, $separator + 1)), '.');
}

/**
 * @param resource $socket
 * @param list<int> $expectedCodes
 */
function portfolioSmtpCommand($socket, string $command, array $expectedCodes): string
{
    portfolioWriteSmtpCommand($socket, $command);
    [$code, $response] = portfolioReadSmtpResponse($socket);
    portfolioRequireSmtpCode($code, $expectedCodes);

    return $response;
}

/**
 * @param resource $socket
 */
function portfolioWriteSmtpCommand($socket, string $command): void
{
    portfolioWriteAll($socket, $command . "\r\n");
}

/**
 * @param resource $socket
 */
function portfolioWriteAll($socket, string $data): void
{
    $length = strlen($data);
    $offset = 0;

    while ($offset < $length) {
        $written = fwrite($socket, substr($data, $offset));

        if ($written === false || $written === 0) {
            throw new RuntimeException('SMTP write failed.');
        }

        $offset += $written;
    }
}

/**
 * @param resource $socket
 * @return array{0: int, 1: string}
 */
function portfolioReadSmtpResponse($socket): array
{
    $response = '';
    $code = 0;

    while (!feof($socket)) {
        $line = fgets($socket, 2048);

        if ($line === false) {
            $metadata = stream_get_meta_data($socket);
            $reason = !empty($metadata['timed_out']) ? 'timeout' : 'read failure';
            throw new RuntimeException("SMTP {$reason}.");
        }

        $response .= $line;

        if (preg_match('/^(\d{3})([ -])/', $line, $matches) !== 1) {
            continue;
        }

        $code = (int) $matches[1];

        if ($matches[2] === ' ') {
            return [$code, $response];
        }
    }

    throw new RuntimeException('SMTP connection closed unexpectedly.');
}

/**
 * @param list<int> $expectedCodes
 */
function portfolioRequireSmtpCode(int $code, array $expectedCodes): void
{
    if (!in_array($code, $expectedCodes, true)) {
        throw new RuntimeException("Unexpected SMTP response code {$code}.");
    }
}

function portfolioBuildSmtpMessage(
    string $recipient,
    string $fromEmail,
    string $replyTo,
    string $subject,
    string $body,
    string $messageIdDomain
): string {
    $normalizedBody = preg_replace("/\r\n|\r|\n/", "\r\n", $body) ?? $body;
    $normalizedBody = preg_replace('/(?m)^\./', '..', $normalizedBody) ?? $normalizedBody;
    $messageId = bin2hex(random_bytes(16));

    $headers = [
        'Date: ' . gmdate('D, d M Y H:i:s') . ' +0000',
        "Message-ID: <{$messageId}@{$messageIdDomain}>",
        "From: Portfolio Sergio <{$fromEmail}>",
        "To: <{$recipient}>",
        "Reply-To: {$replyTo}",
        "Subject: {$subject}",
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: 8bit',
        'X-Mailer: Portfolio Sergio',
    ];

    return implode("\r\n", $headers)
        . "\r\n\r\n"
        . rtrim($normalizedBody, "\r\n");
}
