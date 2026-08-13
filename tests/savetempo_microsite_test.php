<?php
declare(strict_types=1);

$root = dirname(__DIR__);
require_once $root . '/apps/savetempo/components/bootstrap.php';

function assertTrue(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}" . PHP_EOL);
        exit(1);
    }
}

assertTrue(stConfig('slug') === 'savetempo', 'Product slug must be centralized.');
assertTrue(stConfig('basePath') === '/apps/savetempo', 'Default base path must match the public route.');
assertTrue(stConfig('canonicalBaseUrl') === 'https://sergiotech.es/apps/savetempo', 'Canonical base URL must be centralized.');
assertTrue(stConfig('googlePlayUrl') === null, 'Google Play must remain unconfigured.');
assertTrue(stConfig('appStoreUrl') === null, 'App Store must remain unconfigured.');
assertTrue(stStoreLinks('en') === [], 'No store link may render while URLs are null.');
assertTrue(stPath('/privacy') === '/apps/savetempo/privacy', 'Portable paths must use basePath.');
assertTrue(stCanonical('privacy') === 'https://sergiotech.es/apps/savetempo/privacy', 'Canonical legal route must derive from config.');

$routeFiles = [
    $root . '/apps/savetempo/index.php',
    $root . '/apps/savetempo/privacy/index.php',
    $root . '/apps/savetempo/support/index.php',
    $root . '/apps/savetempo/terms/index.php',
];
foreach ($routeFiles as $file) {
    assertTrue(is_file($file), "Missing public route file: {$file}");
}

$_SERVER['REQUEST_URI'] = '/apps/savetempo/';
$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US,en;q=0.9';
$_GET = ['lang' => 'en'];
ob_start();
include $root . '/apps/savetempo/index.php';
$landingEnglish = (string) ob_get_clean();
assertTrue(str_contains($landingEnglish, 'Save at your own tempo.'), 'English landing copy must render.');
assertTrue(str_contains($landingEnglish, 'Your money stays yours.'), 'Money boundary must be explicit.');
assertTrue(!str_contains($landingEnglish, 'href="#"'), 'Landing must not contain placeholder links.');
assertTrue(!str_contains($landingEnglish, 'javascript:void'), 'Landing must not contain JavaScript links.');
assertTrue(!str_contains($landingEnglish, 'Get it on Google Play'), 'Google Play link must stay hidden while null.');
assertTrue(!str_contains($landingEnglish, 'Download on the App Store'), 'App Store link must stay hidden while null.');
assertTrue(substr_count($landingEnglish, 'class="product-phone-screenshot"') === 11, 'Every product screenshot must use the ratio-safe media class.');
assertTrue(str_contains($landingEnglish, 'class="showcase-track" tabindex="0"'), 'The horizontal showcase must be keyboard focusable.');
assertTrue(str_contains($landingEnglish, 'data-showcase-track'), 'The showcase must expose its keyboard navigation hook.');
assertTrue(str_contains($landingEnglish, 'savetempo.css?v=1.0.1'), 'The stylesheet URL must invalidate the previous public cache.');
assertTrue(str_contains($landingEnglish, 'savetempo.js?v=1.0.1'), 'The script URL must invalidate the previous public cache.');

$_SERVER['REQUEST_URI'] = '/apps/savetempo/privacy';
$_GET = ['lang' => 'es'];
ob_start();
include $root . '/apps/savetempo/privacy/index.php';
$privacySpanish = (string) ob_get_clean();
assertTrue(str_contains($privacySpanish, 'Política de privacidad de SaveTempo'), 'Spanish Privacy must render.');
assertTrue(str_contains($privacySpanish, 'smorgarc@sergiotech.es'), 'Privacy contact must be correct.');
assertTrue(str_contains($privacySpanish, '<main id="main-content">'), 'Legal content must be server rendered.');

$_SERVER['REQUEST_URI'] = '/apps/savetempo/support';
$_GET = ['lang' => 'en'];
ob_start();
include $root . '/apps/savetempo/support/index.php';
$supportEnglish = (string) ob_get_clean();
assertTrue(str_contains($supportEnglish, 'mailto:smorgarc@sergiotech.es'), 'Support email must be clickable.');
assertTrue(str_contains($supportEnglish, 'Can lost data be restored?'), 'Lost-data support guidance must exist.');

$_SERVER['REQUEST_URI'] = '/apps/savetempo/terms';
$_GET = ['lang' => 'es'];
ob_start();
include $root . '/apps/savetempo/terms/index.php';
$termsSpanish = (string) ob_get_clean();
assertTrue(str_contains($termsSpanish, 'No es un servicio financiero'), 'Terms must explain the financial-service boundary.');
assertTrue(str_contains($termsSpanish, 'Sin asesoramiento financiero'), 'Terms must contain the advice disclaimer.');

global $productConfig;
$originalGoogle = $productConfig['googlePlayUrl'];
$originalApple = $productConfig['appStoreUrl'];
$productConfig['googlePlayUrl'] = 'https://play.google.com/store/apps/details?id=example';
$productConfig['appStoreUrl'] = 'https://apps.apple.com/app/id123456789';
$configuredLinks = stStoreLinks('en');
assertTrue(count($configuredLinks) === 2, 'Both store links must appear automatically when configured.');
$productConfig['googlePlayUrl'] = $originalGoogle;
$productConfig['appStoreUrl'] = $originalApple;

$portfolio = (string) file_get_contents($root . '/index.php');
assertTrue(str_contains($portfolio, 'data-umami-event-project="savetempo"'), 'Portfolio project analytics hook must identify SaveTempo.');
assertTrue(str_contains($portfolio, 'class="project-card-overlay"'), 'The complete project card must open SaveTempo.');
assertTrue(str_contains($portfolio, "stPath('/')"), 'Portfolio SaveTempo card must use the product base path.');
assertTrue(str_contains($portfolio, 'project-card--savetempo'), 'SaveTempo portfolio styles must remain isolated.');
assertTrue(str_contains($portfolio, 'class="project-phone-screenshot"'), 'Portfolio screenshot must use the ratio-safe media class.');

echo 'SaveTempo microsite tests: PASS' . PHP_EOL;
