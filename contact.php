<?php
declare(strict_types=1);

define('PORTFOLIO_APP', true);
require_once __DIR__ . '/mailer.php';
require_once __DIR__ . '/localization.php';

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';

session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'cookie_secure' => $isHttps,
    'use_strict_mode' => true,
]);

$postedLanguage = portfolioSupportedLanguage($_POST['language'] ?? null);
$language = $postedLanguage ?? portfolioDetectLanguage();
$t = static fn(string $key): string => portfolioText($language, $key);

header("Content-Security-Policy: default-src 'none'; frame-ancestors 'none'");
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store, max-age=0');
header("Content-Language: {$language}");

function respond(bool $success, string $message, int $statusCode = 200, array $extra = []): never
{
    global $language;
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
    $languageQuery = $language === 'en' ? 'lang=en&' : '';
    header("Location: /?{$languageQuery}status={$query}#contacto", true, 303);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    respond(false, $t('contact_method_not_allowed'), 405);
}

$contentLength = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
if ($contentLength > 20_000) {
    respond(false, $t('contact_too_large'), 413);
}

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$host = $_SERVER['HTTP_HOST'] ?? '';
if ($origin !== '') {
    $originHost = parse_url($origin, PHP_URL_HOST);
    $hostWithoutPort = preg_replace('/:\d+$/', '', $host);

    if (!is_string($originHost) || !hash_equals(strtolower((string) $hostWithoutPort), strtolower($originHost))) {
        respond(false, $t('contact_invalid_request'), 403);
    }
}

$postedToken = is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : '';
$sessionToken = is_string($_SESSION['csrf_token'] ?? null) ? $_SESSION['csrf_token'] : '';

if ($postedToken === '' || $sessionToken === '' || !hash_equals($sessionToken, $postedToken)) {
    respond(false, $t('contact_expired'), 419);
}

$honeypot = trim((string) ($_POST['website'] ?? ''));
if ($honeypot !== '') {
    respond(true, $t('form_success'));
}

$startedAt = (int) ($_SESSION['form_started_at'] ?? 0);
if ($startedAt === 0 || time() - $startedAt < 2) {
    respond(false, $t('contact_too_fast'), 429);
}

$lastSubmission = (int) ($_SESSION['last_contact_submission'] ?? 0);
if ($lastSubmission > 0 && time() - $lastSubmission < 60) {
    respond(false, $t('contact_rate_limit'), 429);
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
    respond(false, $t('contact_invalid_fields'), 422);
}

$recipient = getenv('PORTFOLIO_TO_EMAIL') ?: 'smorgarc@sergiotech.es';
$fromEmail = getenv('PORTFOLIO_FROM_EMAIL') ?: 'no-reply@sergiotech.es';

if (
    !filter_var($recipient, FILTER_VALIDATE_EMAIL) ||
    !filter_var($fromEmail, FILTER_VALIDATE_EMAIL) ||
    preg_match('/[\r\n]/', $recipient . $fromEmail) === 1
) {
    error_log('Portfolio contact form: invalid server email configuration.');
    respond(false, $t('contact_unavailable'), 500);
}

$safeSubject = "Portfolio: {$subject}";
if (function_exists('mb_encode_mimeheader')) {
    $safeSubject = mb_encode_mimeheader($safeSubject, 'UTF-8');
}

$body = implode(PHP_EOL, [
    $t('email_heading'),
    '---------------------------------',
    $t('email_name') . ": {$name}",
    $t('email_address') . ": {$email}",
    $t('email_subject') . ": {$subject}",
    '',
    $t('email_message') . ':',
    $message,
    '',
    $t('email_consent'),
    $t('email_date') . ': ' . gmdate('Y-m-d H:i:s'),
]);

$sent = portfolioSendEmail(
    $recipient,
    $fromEmail,
    $email,
    $safeSubject,
    $body
);

if (!$sent) {
    error_log('Portfolio contact form: all email transports rejected a message.');
    respond(false, $t('contact_send_error'), 500);
}

$_SESSION['last_contact_submission'] = time();
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

respond(
    true,
    $t('form_success'),
    200,
    ['csrfToken' => $_SESSION['csrf_token']]
);
