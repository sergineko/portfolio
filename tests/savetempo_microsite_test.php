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
        'status_available' => 'Live',
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
assertTrue(stConfig('privacyPath') === '/privacy/', 'Privacy route must use its redirect-free trailing-slash URL.');
assertTrue(stConfig('supportPath') === '/support/', 'Support route must use its redirect-free trailing-slash URL.');
assertTrue(stConfig('termsPath') === '/terms/', 'Terms route must use its redirect-free trailing-slash URL.');
$officialGooglePlayUrl = 'https://play.google.com/store/apps/details?id=es.sergiotech.savetempo';
assertTrue(stConfig('googlePlayUrl') === $officialGooglePlayUrl, 'Google Play must use the official SaveTempo listing.');
assertTrue(stConfig('appStoreUrl') === null, 'App Store must remain unconfigured.');
$officialStoreLinks = stStoreLinks('en');
assertTrue(count($officialStoreLinks) === 1, 'Only the configured Google Play link may render.');
assertTrue($officialStoreLinks[0]['store'] === 'google', 'The official store link must be Google Play.');
assertTrue($officialStoreLinks[0]['url'] === $officialGooglePlayUrl, 'The official Google Play link must keep its exact destination.');
assertTrue(stConfig('releaseStatus') === 'available', 'The released Android app must not remain marked as coming soon.');
assertTrue(stConfig('supportedPlatforms') === ['Android'], 'Structured data must only advertise the published Android platform.');
assertTrue(stPath('/privacy/') === '/apps/savetempo/privacy/', 'Portable paths must use the final trailing-slash route.');
assertTrue(stCanonical('privacy/') === 'https://sergiotech.es/apps/savetempo/privacy/', 'Canonical legal route must match the final HTTPS URL.');
assertTrue(stRoutePath('landing', 'en') === '/apps/savetempo/', 'English landing must keep the default product route.');
assertTrue(stRoutePath('landing', 'es') === '/es/apps/savetempo/', 'Spanish landing must use an independent path.');
assertTrue(stRouteCanonical('week52', 'en') === 'https://sergiotech.es/apps/savetempo/52-week-savings-challenge/', 'English guide canonical must use its descriptive route.');
assertTrue(stRouteCanonical('week52', 'es') === 'https://sergiotech.es/es/apps/savetempo/reto-ahorro-52-semanas/', 'Spanish guide canonical must use its localized route.');
assertTrue(is_file($root . '/apps/savetempo/assets/images/google-play-badge-en.png'), 'English Google Play badge asset must exist.');
assertTrue(is_file($root . '/apps/savetempo/assets/images/google-play-badge-es.png'), 'Spanish Google Play badge asset must exist.');

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
    $root . '/apps/savetempo/52-week-savings-challenge/index.php',
    $root . '/apps/savetempo/365-day-savings-challenge/index.php',
    $root . '/apps/savetempo/savings-challenge-app/index.php',
    $root . '/es/apps/savetempo/index.php',
    $root . '/es/apps/savetempo/privacy/index.php',
    $root . '/es/apps/savetempo/support/index.php',
    $root . '/es/apps/savetempo/terms/index.php',
    $root . '/es/apps/savetempo/reto-ahorro-52-semanas/index.php',
    $root . '/es/apps/savetempo/reto-ahorro-365-dias/index.php',
    $root . '/es/apps/savetempo/app-retos-ahorro/index.php',
];
foreach ($routeFiles as $file) {
    assertTrue(is_file($file), "Missing public route file: {$file}");
}

$_SERVER['REQUEST_URI'] = '/apps/savetempo/';
$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'es-ES,es;q=0.9';
$_GET = [];
ob_start();
include $root . '/apps/savetempo/index.php';
$landingEnglish = (string) ob_get_clean();
assertTrue(str_contains($landingEnglish, 'Save at your own tempo.'), 'English landing copy must render.');
assertTrue(str_contains($landingEnglish, '<html lang="en"'), 'The clean English path must not change language from Accept-Language.');
assertTrue(str_contains($landingEnglish, 'Your money stays yours.'), 'Money boundary must be explicit.');
assertTrue(!str_contains($landingEnglish, 'href="#"'), 'Landing must not contain placeholder links.');
assertTrue(!str_contains($landingEnglish, 'javascript:void'), 'Landing must not contain JavaScript links.');
assertTrue(str_contains($landingEnglish, 'Get it on Google Play'), 'The configured Google Play link must render.');
assertTrue(str_contains($landingEnglish, 'href="' . $officialGooglePlayUrl . '"'), 'The landing must use the official Google Play destination.');
assertTrue(substr_count($landingEnglish, 'class="google-play-badge"') === 3, 'The official Google Play badge must render in every microsite store placement.');
assertTrue(str_contains($landingEnglish, 'src="/apps/savetempo/assets/images/google-play-badge-en.png"'), 'The English microsite must use the official English Google Play badge.');
assertTrue(!str_contains($landingEnglish, 'Download on the App Store'), 'App Store link must stay hidden while null.');
assertTrue(str_contains($landingEnglish, '<link rel="canonical" href="https://sergiotech.es/apps/savetempo/">'), 'English landing must be self-canonical.');
assertTrue(str_contains($landingEnglish, 'hreflang="es" href="https://sergiotech.es/es/apps/savetempo/"'), 'Landing must expose the clean Spanish alternate URL.');
assertTrue(!str_contains($landingEnglish, '?lang='), 'SaveTempo landing must not publish query-string language links.');
assertTrue(str_contains($landingEnglish, 'max-snippet:-1'), 'Landing must allow full search snippets.');
assertTrue(str_contains($landingEnglish, '"downloadUrl":"' . $officialGooglePlayUrl . '"'), 'Software structured data must reference Google Play.');
assertTrue(str_contains($landingEnglish, '"operatingSystem":"Android"'), 'Software structured data must reflect the published platform.');
assertTrue(substr_count($landingEnglish, 'class="product-phone-screenshot"') === 11, 'Every product screenshot must use the ratio-safe media class.');
assertTrue(str_contains($landingEnglish, 'class="showcase-track" tabindex="0"'), 'The horizontal showcase must be keyboard focusable.');
assertTrue(str_contains($landingEnglish, 'data-showcase-track'), 'The showcase must expose its keyboard navigation hook.');
assertTrue(str_contains($landingEnglish, 'savetempo.css?v=1.0.3'), 'The stylesheet URL must invalidate the previous public cache.');
assertTrue(str_contains($landingEnglish, 'savetempo.js?v=1.0.2'), 'The script URL must invalidate the previous public cache.');
assertTrue(str_contains($landingEnglish, 'src="https://cloud.umami.is/script.js"'), 'Landing must load the shared Umami tracker.');
assertTrue(str_contains($landingEnglish, 'data-website-id="11111111-2222-3333-4444-555555555555"'), 'Landing must use the configured Umami website ID.');
assertTrue(str_contains($landingEnglish, 'data-tag="savetempo-landing-lang-en"'), 'Landing analytics tag must identify route and language.');

$_SERVER['REQUEST_URI'] = '/es/apps/savetempo/';
$_GET = [];
ob_start();
include $root . '/es/apps/savetempo/index.php';
$landingSpanish = (string) ob_get_clean();
assertTrue(str_contains($landingSpanish, '<link rel="canonical" href="https://sergiotech.es/es/apps/savetempo/">'), 'Spanish landing must be self-canonical on its clean path.');
assertTrue(str_contains($landingSpanish, '<title>SaveTempo – App para ahorrar y reto de las 52 semanas</title>'), 'Spanish landing title must target the primary search intent.');
assertTrue(str_contains($landingSpanish, 'Ahorro de hoy'), 'Spanish landing must localize interface labels.');
assertTrue(str_contains($landingSpanish, 'Ahorros pendientes'), 'Spanish landing must localize pending-savings copy.');
assertTrue(!str_contains($landingSpanish, "Today's saving"), 'Spanish landing must not leak English interface labels.');
assertTrue(str_contains($landingSpanish, '/es/apps/savetempo/reto-ahorro-52-semanas/'), 'Spanish landing must link to the localized 52-week guide.');

$_SERVER['REQUEST_URI'] = '/es/apps/savetempo/privacy/';
$_GET = [];
ob_start();
include $root . '/es/apps/savetempo/privacy/index.php';
$privacySpanish = (string) ob_get_clean();
assertTrue(str_contains($privacySpanish, 'Política de privacidad de SaveTempo'), 'Spanish Privacy must render.');
assertTrue(str_contains($privacySpanish, 'smorgarc@sergiotech.es'), 'Privacy contact must be correct.');
assertTrue(str_contains($privacySpanish, '<main id="main-content">'), 'Legal content must be server rendered.');
assertTrue(str_contains($privacySpanish, 'data-tag="savetempo-privacy-lang-es"'), 'Privacy analytics tag must identify route and language.');
assertTrue(str_contains($privacySpanish, 'Cuando Umami está configurado'), 'Privacy copy must disclose website analytics.');
assertTrue(str_contains($privacySpanish, '<link rel="canonical" href="https://sergiotech.es/es/apps/savetempo/privacy/">'), 'Spanish privacy page must be self-canonical.');
assertTrue(str_contains($privacySpanish, 'hreflang="en" href="https://sergiotech.es/apps/savetempo/privacy/"'), 'Privacy page must expose its reciprocal English alternate.');
assertTrue(!str_contains($privacySpanish, '?lang='), 'Spanish privacy page must not publish query-string language links.');

$_SERVER['REQUEST_URI'] = '/apps/savetempo/support/';
$_GET = [];
ob_start();
include $root . '/apps/savetempo/support/index.php';
$supportEnglish = (string) ob_get_clean();
assertTrue(str_contains($supportEnglish, 'mailto:smorgarc@sergiotech.es'), 'Support email must be clickable.');
assertTrue(str_contains($supportEnglish, 'Can lost data be restored?'), 'Lost-data support guidance must exist.');
assertTrue(str_contains($supportEnglish, 'data-tag="savetempo-support-lang-en"'), 'Support analytics tag must identify route and language.');

$_SERVER['REQUEST_URI'] = '/es/apps/savetempo/terms/';
$_GET = [];
ob_start();
include $root . '/es/apps/savetempo/terms/index.php';
$termsSpanish = (string) ob_get_clean();
assertTrue(str_contains($termsSpanish, 'No es un servicio financiero'), 'Terms must explain the financial-service boundary.');
assertTrue(str_contains($termsSpanish, 'Sin asesoramiento financiero'), 'Terms must contain the advice disclaimer.');
assertTrue(str_contains($termsSpanish, 'data-tag="savetempo-terms-lang-es"'), 'Terms analytics tag must identify route and language.');

$_SERVER['REQUEST_URI'] = '/apps/savetempo/52-week-savings-challenge/';
$_GET = [];
ob_start();
include $root . '/apps/savetempo/52-week-savings-challenge/index.php';
$week52English = (string) ob_get_clean();
assertTrue(str_contains($week52English, '52 Week Savings Challenge: save €1,378 in one year'), 'English 52-week guide must render a search-focused H1.');
assertTrue(str_contains($week52English, '<link rel="canonical" href="https://sergiotech.es/apps/savetempo/52-week-savings-challenge/">'), 'English 52-week guide must be self-canonical.');
assertTrue(substr_count($week52English, 'data-challenge-amount') === 52, '52-week guide must server-render every weekly contribution.');
assertTrue(str_contains($week52English, '1,378.00 €'), '52-week guide must expose the correct default total.');
assertTrue(str_contains($week52English, '"@type":"Article"'), 'Guide structured data must identify an Article.');
assertTrue(str_contains($week52English, 'property="og:type" content="article"'), 'Guide social metadata must identify article content.');
assertTrue(str_contains($week52English, '"datePublished":"2026-09-06"'), 'Guide Article data must expose a publication date.');

$_SERVER['REQUEST_URI'] = '/es/apps/savetempo/reto-ahorro-52-semanas/';
$_GET = [];
ob_start();
include $root . '/es/apps/savetempo/reto-ahorro-52-semanas/index.php';
$week52Spanish = (string) ob_get_clean();
assertTrue(str_contains($week52Spanish, 'Reto de ahorro de 52 semanas: ahorra 1.378 € en un año'), 'Spanish 52-week guide must render localized useful content.');
assertTrue(str_contains($week52Spanish, '<link rel="canonical" href="https://sergiotech.es/es/apps/savetempo/reto-ahorro-52-semanas/">'), 'Spanish 52-week guide must be self-canonical.');
assertTrue(str_contains($week52Spanish, 'hreflang="en" href="https://sergiotech.es/apps/savetempo/52-week-savings-challenge/"'), 'Spanish guide must expose its reciprocal English alternate.');

$_SERVER['REQUEST_URI'] = '/apps/savetempo/365-day-savings-challenge/';
$_GET = [];
ob_start();
include $root . '/apps/savetempo/365-day-savings-challenge/index.php';
$day365English = (string) ob_get_clean();
assertTrue(str_contains($day365English, '365 Day Savings Challenge: save €667.95 one day at a time'), '365-day guide must render a search-focused H1.');
assertTrue(str_contains($day365English, '667.95 €'), '365-day guide must expose the correct default total.');

$_SERVER['REQUEST_URI'] = '/es/apps/savetempo/app-retos-ahorro/';
$_GET = [];
ob_start();
include $root . '/es/apps/savetempo/app-retos-ahorro/index.php';
$challengeAppSpanish = (string) ob_get_clean();
assertTrue(str_contains($challengeAppSpanish, 'Planifica y registra un reto de ahorro sin conectar tu banco'), 'Spanish savings challenge app guide must render substantive content.');
assertTrue(substr_count($challengeAppSpanish, '<section><span>0') === 6, 'Savings challenge app guide must render all six feature cards.');

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
        if ($store === 'google') {
            $badge = $xpath->query(".//img[contains(concat(' ', normalize-space(@class), ' '), ' google-play-badge ')]", $storeElement);
            assertTrue($badge !== false && $badge->length === 1, "{$stateName}: Google Play must use the official badge image.");
            assertTrue($badge->item(0)?->attributes?->getNamedItem('src')?->nodeValue === '/apps/savetempo/assets/images/google-play-badge-en.png', "{$stateName}: Google Play badge must match the card language.");
        }
    }
}

$sitemap = new DOMDocument();
assertTrue($sitemap->load($root . '/sitemap.xml'), 'Sitemap must be valid XML.');
$sitemapXpath = new DOMXPath($sitemap);
$sitemapXpath->registerNamespace('sm', 'http://www.sitemaps.org/schemas/sitemap/0.9');
$sitemapLocations = [];
foreach ($sitemapXpath->query('//sm:url/sm:loc') ?: [] as $location) {
    $sitemapLocations[] = $location->textContent;
}
foreach ([
    'https://sergiotech.es/apps/savetempo/',
    'https://sergiotech.es/es/apps/savetempo/',
    'https://sergiotech.es/apps/savetempo/privacy/',
    'https://sergiotech.es/es/apps/savetempo/privacy/',
    'https://sergiotech.es/apps/savetempo/52-week-savings-challenge/',
    'https://sergiotech.es/es/apps/savetempo/reto-ahorro-52-semanas/',
    'https://sergiotech.es/apps/savetempo/365-day-savings-challenge/',
    'https://sergiotech.es/es/apps/savetempo/reto-ahorro-365-dias/',
    'https://sergiotech.es/apps/savetempo/savings-challenge-app/',
    'https://sergiotech.es/es/apps/savetempo/app-retos-ahorro/',
] as $expectedLocation) {
    assertTrue(in_array($expectedLocation, $sitemapLocations, true), "Sitemap must contain {$expectedLocation}.");
}
assertTrue(!in_array('https://sergiotech.es/apps/savetempo/privacy', $sitemapLocations, true), 'Sitemap must not publish redirecting legal URLs.');
$sitemapSource = (string) file_get_contents($root . '/sitemap.xml');
assertTrue(!str_contains($sitemapSource, '/apps/savetempo/?lang='), 'SaveTempo sitemap must not publish query-string language URLs.');
assertTrue(!str_contains($sitemapSource, '/apps/savetempo/privacy/?lang='), 'SaveTempo legal sitemap entries must use clean language paths.');

$htaccess = (string) file_get_contents($root . '/.htaccess');
assertTrue(str_contains($htaccess, 'RewriteRule ^apps/savetempo/(privacy|support|terms)$'), 'Legacy no-slash legal URLs must redirect directly to HTTPS canonical URLs.');
assertTrue(str_contains($htaccess, 'lang=es'), 'Legacy Spanish query URLs must permanently redirect to clean paths.');

$portfolioSource = (string) file_get_contents($root . '/index.php');
assertTrue(str_contains($portfolioSource, 'renderSaveTempoProjectCard($language, $t)'), 'Portfolio must render the tested SaveTempo card component.');
assertTrue(str_contains($portfolioSource, 'umamiConfiguration($siteUrl)'), 'Portfolio must use the shared Umami configuration.');
assertTrue(str_contains($portfolioSource, 'styles.css?v=2.3.1'), 'Portfolio stylesheet must invalidate the previous public cache.');

foreach ($umamiEnvironment as $name => $value) {
    putenv($value === false ? $name : "{$name}={$value}");
}

echo 'SaveTempo microsite tests: PASS' . PHP_EOL;
