<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/mailer.php';

function runFakeSmtpServer(): never
{
    $errorCode = 0;
    $errorMessage = '';
    $server = stream_socket_server(
        'tcp://127.0.0.1:0',
        $errorCode,
        $errorMessage,
        STREAM_SERVER_BIND | STREAM_SERVER_LISTEN
    );

    if (!is_resource($server)) {
        fwrite(STDERR, "No se pudo crear el SMTP simulado.\n");
        exit(2);
    }

    $serverAddress = stream_socket_get_name($server, false);
    $portSeparator = strrpos((string) $serverAddress, ':');
    $port = (int) substr((string) $serverAddress, $portSeparator + 1);
    fwrite(STDOUT, $port . "\n");
    fflush(STDOUT);

    $client = stream_socket_accept($server, 10);

    if (!is_resource($client)) {
        exit(3);
    }

    fwrite($client, "220 smtp.test ESMTP\r\n");
    $commands = '';
    $message = '';

    while (($line = fgets($client, 2048)) !== false) {
        $commands .= $line;
        $command = strtoupper(trim($line));

        if (str_starts_with($command, 'EHLO ')) {
            fwrite($client, "250-smtp.test\r\n250 PIPELINING\r\n");
        } elseif (str_starts_with($command, 'MAIL FROM:')) {
            fwrite($client, "250 2.1.0 Sender accepted\r\n");
        } elseif (str_starts_with($command, 'RCPT TO:')) {
            fwrite($client, "250 2.1.5 Recipient accepted\r\n");
        } elseif ($command === 'DATA') {
            fwrite($client, "354 End data with <CR><LF>.<CR><LF>\r\n");

            while (($dataLine = fgets($client, 2048)) !== false) {
                if ($dataLine === ".\r\n") {
                    break;
                }

                $message .= $dataLine;
            }

            fwrite($client, "250 2.0.0 Queued\r\n");
        } elseif ($command === 'QUIT') {
            fwrite($client, "221 2.0.0 Bye\r\n");
            break;
        } else {
            fwrite($client, "500 5.5.2 Unexpected command\r\n");
        }
    }

    fclose($client);
    fclose($server);

    $valid =
        str_contains($commands, 'MAIL FROM:<no-reply@sergiotech.es>') &&
        str_contains($commands, 'RCPT TO:<smorgarc@sergiotech.es>') &&
        str_contains($message, 'Subject: Portfolio: Prueba SMTP') &&
        str_contains($message, 'Reply-To: visitante@example.com') &&
        str_contains($message, '..Línea que comienza por punto');

    exit($valid ? 0 : 4);
}

if (($argv[1] ?? '') === '--server') {
    runFakeSmtpServer();
}

$descriptors = [
    0 => ['pipe', 'r'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w'],
];
$process = proc_open([PHP_BINARY, __FILE__, '--server'], $descriptors, $pipes);

if (!is_resource($process)) {
    fwrite(STDERR, "No se pudo iniciar el SMTP simulado.\n");
    exit(1);
}

fclose($pipes[0]);
$port = (int) trim((string) fgets($pipes[1]));

if ($port <= 0) {
    fwrite(STDERR, "El SMTP simulado no devolvió un puerto válido.\n");
    proc_terminate($process);
    exit(1);
}

$sent = portfolioSendViaMx(
    'smorgarc@sergiotech.es',
    'no-reply@sergiotech.es',
    'visitante@example.com',
    'Portfolio: Prueba SMTP',
    "Mensaje de prueba\r\n.Línea que comienza por punto",
    ['127.0.0.1'],
    $port,
    false
);

$serverOutput = stream_get_contents($pipes[1]);
$serverError = stream_get_contents($pipes[2]);
fclose($pipes[1]);
fclose($pipes[2]);
$serverStatus = proc_close($process);

if (!$sent || $serverStatus !== 0) {
    fwrite(STDERR, $serverOutput . $serverError);
    fwrite(STDERR, "La prueba SMTP ha fallado.\n");
    exit(1);
}

fwrite(STDOUT, "Prueba SMTP superada.\n");
