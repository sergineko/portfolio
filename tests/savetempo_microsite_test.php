<?php
declare(strict_types=1);

$root = dirname(__DIR__);
require_once $root . '/apps/savetempo/components/bootstrap.php';
require_once $root . '/components/savetempo-project-card.php';

function assertTrue(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}" . PHP_EOL);
        exit(1);
    }
}

function renderSaveTempoCardForStores(?string $googlePlayUrl, ?string $appStoreUrl): string
{
    global $productConfig;
    $originalGoogle = $productConfig['googlePlayUrl'];
    $originalApple = $productConfig['appStoreUrl'];
    $copy = [
        'view_savetempo' => 'View SaveTempo',
        'status_coming_soon' => 'Coming soon',
        'savetempo_type' => 'Savings habit planner',
        'savetempo_description' => 'SaveTempo description',
        'features_label' => 'Features',
        'tag_local_first' => 'Local-first',
        'savetempo_alt' => 'SaveTempo Home',
    ];

    try {
        $productConfig['googlePlayUrl'] = $googlePlayUrl;
        $productConfig['appStoreUrl'] = $appStoreUrl;
        ob_start();
        renderSaveTempoProjectCard('en', static fn(string $key): string => $copy[$key] ?? $key);
        return (string) ob_get_clean();
    } finally {
        $productConfig['googlePlayUrl'] = $originalGoogle;
        $productConfig['appStoreUrl'] = $originalApple;
    }
}

/** @return array{dom: DOMDocument, xpath: DOMXPath, card: DOMElement} */
function parseSaveTempoCard(string $markup): array
{
    $dom = new DOMDocument();
    $previousErrors = libxml_use_internal_errors(true);
    $dom->loadHTML('<!doctype html><html><body>' . $markup . '</body></html>');
    libxml_clear_errors();
    libxml_use_internal_errors($previousErrors);
    $xpath = new DOMXPath($dom);
    $cards = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' project-card--savetempo ')]");
    assertTrue($cards !== false && $cards->length === 1, 'Rendered markup must contain one SaveTempo card.');
    $card = $cards->item(0);
    assertTrue($card instanceof DOMElement, 'SaveTempo card must be an HTML element.');
    return ['dom' => $dom, 'xpath' => $xpath, 'card' => $card];
}

assertTrue(stConfig('slug') === 'savetempo', 'Product slug must be centralized.');
assertTrue(stConfig('basePath') === '/apps/savetempo', 'Default base path must match the public route.');
assertTrue(stConfig('canonicalBaseUrl') === 'https://sergiotech.es/apps/savetempo', 'Canonical base URL must be centralized.');
assertTrue(stConfig('googlePlayUrl') === null, 'Google Play must remain unconfigured.');
assertTrue(stConfig('appStoreUrl') === null, 'App Store must remain unconfigured.');
assertTrue(stStoreLinks('en') === [], 'No store link may render while URLs are null.');
assertTrue(stPath('/privacy') === '/apps/savetempo/privacy', 'Portable paths must use basePath.');
assertTrue(stCanonical('privacy') === 'https://sergiotech.es/apps/savetempo/privacy', 'Canonical legal route must derive from config.');

$umamiEnvironment = [
    'UMAMI_SCRIPT_URL' => getenv('UMAMI_SCRIPT_URL'),
    'UMAMI_WEBSITE_ID' => getenv('UMAMI_WEBSITE_ID'),
    'UMAMI_DOMAINS' => getenv('UMAMI_DOMAINS'),
];
putenv('UMAMI_SCRIPT_URL=http://cloud.umami.is/script.js');
putenv('UMAMI_WEBSITE_ID=not-a-uuid');
putenv('UMAMI_DOMAINS=sergiotech.es');
$invalidUmamiConfig = umamiConfiguration('https://sergiotech.es');
assertTrue($invalidUmamiConfig['enabled'] === false, 'Umami must stay disabled for an insecure URL and invalid website ID.');
ob_start();
umamiRenderTrackingScript($invalidUmamiConfig, 'invalid');
assertTrue((string) ob_get_clean() === '', 'Disabled Umami configuration must not render a tracker.');

putenv('UMAMI_SCRIPT_URL=https://cloud.umami.is/script.js');
putenv('UMAMI_WEBSITE_ID=11111111-2222-3333-4444-555555555555');
putenv('UMAMI_DOMAINS=sergiotech.es');
$validUmamiConfig = umamiConfiguration('https://sergiotech.es');
assertTrue($validUmamiConfig['enabled'] === true, 'Valid Umami configuration must enable analytics.');
assertTrue($validUmamiConfig['origin'] === 'https://cloud.umami.is', 'Umami origin must be normalized for CSP.');
$saveTempoCsp = stContentSecurityPolicy('test-nonce', $validUmamiConfig, true);
assertTrue(str_contains($saveTempoCsp, "connect-src 'self' https://cloud.umami.is"), 'SaveTempo CSP must allow Umami event delivery.');
assertTrue(str_contains($saveTempoCsp, "script-src 'self' 'nonce-test-nonce' https://cloud.umami.is"), 'SaveTempo CSP must allow the Umami tracker script.');

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
assertTrue(str_contains($landingEnglish, 'src="https://cloud.umami.is/script.js"'), 'Landing must load the shared Umami tracker.');
assertTrue(str_contains($landingEnglish, 'data-website-id="11111111-2222-3333-4444-555555555555"'), 'Landing must use the configured Umami website ID.');
assertTrue(str_contains($landingEnglish, 'data-tag="savetempo-landing-lang-en"'), 'Landing analytics tag must identify route and language.');

$_SERVER['REQUEST_URI'] = '/apps/savetempo/privacy';
$_GET = ['lang' => 'es'];
ob_start();
include $root . '/apps/savetempo/privacy/index.php';
$privacySpanish = (string) ob_get_clean();
assertTrue(str_contains($privacySpanish, 'Política de privacidad de SaveTempo'), 'Spanish Privacy must render.');
assertTrue(str_contains($privacySpanish, 'smorgarc@sergiotech.es'), 'Privacy contact must be correct.');
assertTrue(str_contains($privacySpanish, '<main id="main-content">'), 'Legal content must be server rendered.');
assertTrue(str_contains($privacySpanish, 'data-tag="savetempo-privacy-lang-es"'), 'Privacy analytics tag must identify route and language.');
assertTrue(str_contains($privacySpanish, 'Cuando Umami está configurado'), 'Privacy copy must disclose website analytics.');

$_SERVER['REQUEST_URI'] = '/apps/savetempo/support';
$_GET = ['lang' => 'en'];
ob_start();
include $root . '/apps/savetempo/support/index.php';
$supportEnglish = (string) ob_get_clean();
assertTrue(str_contains($supportEnglish, 'mailto:smorgarc@sergiotech.es'), 'Support email must be clickable.');
assertTrue(str_contains($supportEnglish, 'Can lost data be restored?'), 'Lost-data support guidance must exist.');
assertTrue(str_contains($supportEnglish, 'data-tag="savetempo-support-lang-en"'), 'Support analytics tag must identify route and language.');

$_SERVER['REQUEST_URI'] = '/apps/savetempo/terms';
$_GET = ['lang' => 'es'];
ob_start();
include $root . '/apps/savetempo/terms/index.php';
$termsSpanish = (string) ob_get_clean();
assertTrue(str_contains($termsSpanish, 'No es un servicio financiero'), 'Terms must explain the financial-service boundary.');
assertTrue(str_contains($termsSpanish, 'Sin asesoramiento financiero'), 'Terms must contain the advice disclaimer.');
assertTrue(str_contains($termsSpanish, 'data-tag="savetempo-terms-lang-es"'), 'Terms analytics tag must identify route and language.');

global $productConfig;
$originalGoogle = $productConfig['googlePlayUrl'];
$originalApple = $productConfig['appStoreUrl'];
$productConfig['googlePlayUrl'] = 'https://play.google.com/store/apps/details?id=example';
$productConfig['appStoreUrl'] = 'https://apps.apple.com/app/id123456789';
$configuredLinks = stStoreLinks('en');
assertTrue(count($configuredLinks) === 2, 'Both store links must appear automatically when configured.');
$productConfig['googlePlayUrl'] = $originalGoogle;
$productConfig['appStoreUrl'] = $originalApple;

$storeStates = [
    'without stores' => [null, null, []],
    'Google Play only' => ['https://play.google.com/store/apps/details?id=example', null, [
        'google' => 'https://play.google.com/store/apps/details?id=example',
    ]],
    'App Store only' => [null, 'https://apps.apple.com/app/id123456789', [
        'apple' => 'https://apps.apple.com/app/id123456789',
    ]],
    'both stores' => [
        'https://play.google.com/store/apps/details?id=example',
        'https://apps.apple.com/app/id123456789',
        [
            'google' => 'https://play.google.com/store/apps/details?id=example',
            'apple' => 'https://apps.apple.com/app/id123456789',
        ],
    ],
];

foreach ($storeStates as $stateName => [$googleUrl, $appleUrl, $expectedStores]) {
    $cardMarkup = renderSaveTempoCardForStores($googleUrl, $appleUrl);
    $parsedCard = parseSaveTempoCard($cardMarkup);
    $xpath = $parsedCard['xpath'];
    $card = $parsedCard['card'];
    $mainLinks = $xpath->query(".//a[contains(concat(' ', normalize-space(@class), ' '), ' project-card-overlay ')]", $card);
    $storeLinks = $xpath->query(".//a[contains(concat(' ', normalize-space(@class), ' '), ' project-store-link ')]", $card);
    $allLinks = $xpath->query('.//a', $card);
    $titleLinks = $xpath->query('.//h3//a', $card);
    $visualCtas = $xpath->query(".//*[contains(concat(' ', normalize-space(@class), ' '), ' project-link ')]", $card);

    assertTrue($mainLinks !== false && $mainLinks->length === 1, "{$stateName}: card must expose exactly one main link.");
    assertTrue($mainLinks->item(0)?->attributes?->getNamedItem('href')?->nodeValue === '/apps/savetempo/', "{$stateName}: main link must open the microsite.");
    assertTrue($storeLinks !== false && $storeLinks->length === count($expectedStores), "{$stateName}: store-link count must match configuration.");
    assertTrue($allLinks !== false && $allLinks->length === 1 + count($expectedStores), "{$stateName}: no duplicate links to the microsite may render.");
    assertTrue($titleLinks !== false && $titleLinks->length === 0, "{$stateName}: title must not be a duplicate link.");
    assertTrue($visualCtas !== false && $visualCtas->length === 1 && $visualCtas->item(0)?->nodeName === 'span', "{$stateName}: CTA must remain visual without becoming a duplicate link.");
    assertTrue(!$card->hasAttribute('onclick'), "{$stateName}: card must not depend on a JavaScript click handler.");
    assertTrue(!str_contains($cardMarkup, 'href="#"') && !str_contains($cardMarkup, 'javascript:void'), "{$stateName}: placeholder links must never render.");

    foreach ($expectedStores as $store => $expectedUrl) {
        $matchingStore = $xpath->query(".//a[contains(concat(' ', normalize-space(@class), ' '), ' project-store-link--{$store} ')]", $card);
        assertTrue($matchingStore !== false && $matchingStore->length === 1, "{$stateName}: configured {$store} link must render once.");
        $storeElement = $matchingStore->item(0);
        assertTrue($storeElement?->attributes?->getNamedItem('href')?->nodeValue === $expectedUrl, "{$stateName}: {$store} link must keep its exclusive destination.");
        assertTrue($storeElement?->parentNode?->nodeName !== 'a', "{$stateName}: store links must never be nested inside the main link.");
    }
}

$portfolioSource = (string) file_get_contents($root . '/index.php');
assertTrue(str_contains($portfolioSource, 'renderSaveTempoProjectCard($language, $t)'), 'Portfolio must render the tested SaveTempo card component.');
assertTrue(str_contains($portfolioSource, 'umamiConfiguration($siteUrl)'), 'Portfolio must use the shared Umami configuration.');

foreach ($umamiEnvironment as $name => $value) {
    putenv($value === false ? $name : "{$name}={$value}");
}

echo 'SaveTempo microsite tests: PASS' . PHP_EOL;
