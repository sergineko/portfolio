<?php
declare(strict_types=1);

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';

session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'cookie_secure' => $isHttps,
    'use_strict_mode' => true,
]);

header("Content-Security-Policy: default-src 'none'; frame-ancestors 'none'");
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store, max-age=0');

function respond(bool $success, string $message, int $statusCode = 200, array $extra = []): never
{
    http_response_code($statusCode);

    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    $requestedWith = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
    $expectsJson = str_contains($accept, 'application/json') || $requestedWith === 'xmlhttprequest';

    if ($expectsJson) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(
            array_merge(['success' => $success, 'message' => $message], $extra),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
        exit;
    }

    $query = $success ? 'success' : 'error';
    header("Location: index.php?status={$query}#contacto", true, 303);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    respond(false, 'Método no permitido.', 405);
}

$contentLength = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
if ($contentLength > 20_000) {
    respond(false, 'La solicitud es demasiado grande.', 413);
}

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$host = $_SERVER['HTTP_HOST'] ?? '';
if ($origin !== '') {
    $originHost = parse_url($origin, PHP_URL_HOST);
    $hostWithoutPort = preg_replace('/:\d+$/', '', $host);

    if (!is_string($originHost) || !hash_equals(strtolower((string) $hostWithoutPort), strtolower($originHost))) {
        respond(false, 'La solicitud no es válida.', 403);
    }
}

$postedToken = is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : '';
$sessionToken = is_string($_SESSION['csrf_token'] ?? null) ? $_SESSION['csrf_token'] : '';

if ($postedToken === '' || $sessionToken === '' || !hash_equals($sessionToken, $postedToken)) {
    respond(false, 'La sesión ha caducado. Recarga la página e inténtalo de nuevo.', 419);
}

$honeypot = trim((string) ($_POST['website'] ?? ''));
if ($honeypot !== '') {
    respond(true, '¡Gracias! Tu mensaje se ha enviado correctamente.');
}

$startedAt = (int) ($_SESSION['form_started_at'] ?? 0);
if ($startedAt === 0 || time() - $startedAt < 2) {
    respond(false, 'Espera un instante antes de enviar el formulario.', 429);
}

$lastSubmission = (int) ($_SESSION['last_contact_submission'] ?? 0);
if ($lastSubmission > 0 && time() - $lastSubmission < 60) {
    respond(false, 'Espera un minuto antes de enviar otro mensaje.', 429);
}

$name = trim((string) ($_POST['name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$subject = trim((string) ($_POST['subject'] ?? ''));
$message = trim((string) ($_POST['message'] ?? ''));
$privacy = (string) ($_POST['privacy'] ?? '');

$length = static function (string $value): int {
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
};

if (
    $name === '' ||
    $length($name) > 80 ||
    preg_match('/[\r\n]/', $name) === 1 ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    $length($email) > 120 ||
    preg_match('/[\r\n]/', $email) === 1 ||
    $subject === '' ||
    $length($subject) > 120 ||
    preg_match('/[\r\n]/', $subject) === 1 ||
    $length($message) < 20 ||
    $length($message) > 5000 ||
    $privacy !== 'accepted'
) {
    respond(false, 'Revisa los campos obligatorios e inténtalo de nuevo.', 422);
}

$recipient = getenv('PORTFOLIO_TO_EMAIL') ?: 'smorgarc@sergiotech.es';
$fromEmail = getenv('PORTFOLIO_FROM_EMAIL') ?: 'no-reply@sergiotech.es';

if (
    !filter_var($recipient, FILTER_VALIDATE_EMAIL) ||
    !filter_var($fromEmail, FILTER_VALIDATE_EMAIL) ||
    preg_match('/[\r\n]/', $recipient . $fromEmail) === 1
) {
    error_log('Portfolio contact form: invalid server email configuration.');
    respond(false, 'El formulario no está disponible temporalmente. Escríbeme directamente por correo.', 500);
}

$safeSubject = "Portfolio: {$subject}";
if (function_exists('mb_encode_mimeheader')) {
    $safeSubject = mb_encode_mimeheader($safeSubject, 'UTF-8');
}

$body = implode(PHP_EOL, [
    'Nuevo mensaje desde el portfolio',
    '---------------------------------',
    "Nombre: {$name}",
    "Correo: {$email}",
    "Asunto: {$subject}",
    '',
    'Mensaje:',
    $message,
    '',
    'Consentimiento de privacidad: aceptado',
    'Fecha (UTC): ' . gmdate('Y-m-d H:i:s'),
]);

$headers = implode("\r\n", [
    "From: Portfolio Sergio <{$fromEmail}>",
    "Reply-To: {$email}",
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'Content-Transfer-Encoding: 8bit',
    'X-Mailer: PHP/' . PHP_VERSION,
]);

$sent = mail($recipient, $safeSubject, $body, $headers);

if (!$sent) {
    error_log('Portfolio contact form: mail transport rejected a message.');
    respond(false, 'No se ha podido enviar el mensaje. Escríbeme directamente a smorgarc@sergiotech.es.', 500);
}

$_SESSION['last_contact_submission'] = time();
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

respond(
    true,
    '¡Gracias! Tu mensaje se ha enviado correctamente.',
    200,
    ['csrfToken' => $_SESSION['csrf_token']]
);
