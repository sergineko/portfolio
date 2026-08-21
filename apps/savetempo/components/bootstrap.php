<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../components/umami.php';

/** @var array<string, mixed> $productConfig */
$productConfig = require __DIR__ . '/../config/product.php';
/** @var array<string, array<string, mixed>> $productTranslations */
$productTranslations = require __DIR__ . '/../content/translations.php';

function stConfig(?string $key = null): mixed
{
    global $productConfig;
    return $key === null ? $productConfig : ($productConfig[$key] ?? null);
}

function stEscape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function stLanguage(): string
{
    $supported = stConfig('locales');
    $requested = strtolower(trim((string) ($_GET['lang'] ?? '')));

    if (is_array($supported) && in_array($requested, $supported, true)) {
        return $requested;
    }

    $accepted = strtolower((string) ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''));
    foreach (explode(',', $accepted) as $candidate) {
        $locale = substr(trim($candidate), 0, 2);
        if (is_array($supported) && in_array($locale, $supported, true)) {
            return $locale;
        }
    }

    return (string) stConfig('defaultLocale');
}

function stCopy(string $path, ?string $language = null): mixed
{
    global $productTranslations;
    $language ??= stLanguage();
    $value = $productTranslations[$language] ?? $productTranslations[(string) stConfig('defaultLocale')];

    foreach (explode('.', $path) as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $path;
        }
        $value = $value[$segment];
    }

    return $value;
}

function stPath(string $suffix = ''): string
{
    $base = (string) stConfig('basePath');
    $suffix = '/' . ltrim($suffix, '/');

    if ($base === '/') {
        return $suffix === '/' ? '/' : $suffix;
    }

    return rtrim($base, '/') . ($suffix === '/' ? '/' : $suffix);
}

function stCanonical(string $suffix = ''): string
{
    $base = rtrim((string) stConfig('canonicalBaseUrl'), '/');
    $suffix = trim($suffix, '/');
    return $suffix === '' ? $base . '/' : $base . '/' . $suffix;
}

/** @return list<array{store: string, url: string, label: string}> */
function stStoreLinks(?string $language = null): array
{
    $language ??= stLanguage();
    $links = [];
    $googlePlayUrl = stConfig('googlePlayUrl');
    $appStoreUrl = stConfig('appStoreUrl');

    if (is_string($googlePlayUrl) && filter_var($googlePlayUrl, FILTER_VALIDATE_URL)) {
        $links[] = ['store' => 'google', 'url' => $googlePlayUrl, 'label' => (string) stCopy('chrome.storeGoogle', $language)];
    }
    if (is_string($appStoreUrl) && filter_var($appStoreUrl, FILTER_VALIDATE_URL)) {
        $links[] = ['store' => 'apple', 'url' => $appStoreUrl, 'label' => (string) stCopy('chrome.storeApple', $language)];
    }

    return $links;
}

function stAsset(string $key, ?string $language = null): string
{
    $assets = stConfig('assets');
    $asset = is_array($assets) ? ($assets[$key] ?? '') : '';
    if (is_array($asset)) {
        $language ??= stLanguage();
        $asset = $asset[$language] ?? $asset[(string) stConfig('defaultLocale')] ?? '';
    }
    return stPath((string) $asset);
}

function stCurrentProductRoute(): string
{
    $requestPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? stPath('/')), PHP_URL_PATH);
    $requestPath = is_string($requestPath) ? $requestPath : stPath('/');
    $known = [
        stPath('/') => stPath('/'),
        rtrim(stPath('/'), '/') => stPath('/'),
        stPath((string) stConfig('privacyPath')) => stPath((string) stConfig('privacyPath')),
        stPath((string) stConfig('supportPath')) => stPath((string) stConfig('supportPath')),
        stPath((string) stConfig('termsPath')) => stPath((string) stConfig('termsPath')),
    ];
    return $known[$requestPath] ?? stPath('/');
}

/**
 * @param array{enabled: bool, scriptUrl: string, websiteId: string, origin: string, domains: string} $umamiConfig
 */
function stContentSecurityPolicy(string $nonce, array $umamiConfig, bool $isHttps): string
{
    $scriptSources = "'self' 'nonce-{$nonce}'";
    $connectSources = "'self'";
    if ($umamiConfig['enabled']) {
        $scriptSources .= " {$umamiConfig['origin']}";
        $connectSources .= " {$umamiConfig['origin']}";
    }

    $policy = "default-src 'self'; base-uri 'self'; connect-src {$connectSources}; font-src 'self'; "
        . "form-action 'none'; frame-ancestors 'none'; img-src 'self' data:; object-src 'none'; "
        . "script-src {$scriptSources}; style-src 'self'";
    if ($isHttps) {
        $policy .= '; upgrade-insecure-requests';
    }

    return $policy;
}

/**
 * @param array{enabled: bool, scriptUrl: string, websiteId: string, origin: string, domains: string} $umamiConfig
 */
function stSendHeaders(string $language, string $nonce, array $umamiConfig): void
{
    if (headers_sent()) {
        return;
    }

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
    $csp = stContentSecurityPolicy($nonce, $umamiConfig, $isHttps);

    header("Content-Security-Policy: {$csp}");
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-Content-Type-Options: nosniff');
    header('Permissions-Policy: camera=(), geolocation=(), microphone=(), payment=(), usb=()');
    header("Content-Language: {$language}");
    header('Vary: Accept-Language', false);
}

function stRenderStoreLinks(string $className = 'store-links'): void
{
    $links = stStoreLinks();
    if ($links === []) {
        return;
    }
    echo '<div class="' . stEscape($className) . '">';
    foreach ($links as $link) {
        echo '<a class="store-link store-link-' . stEscape($link['store']) . '" href="' . stEscape($link['url']) . '" target="_blank" rel="noopener noreferrer">';
        echo '<span aria-hidden="true">' . ($link['store'] === 'apple' ? '●' : '▶') . '</span>';
        echo stEscape($link['label']) . '</a>';
    }
    echo '</div>';
}

function stRenderPage(string $page, callable $renderBody): void
{
    $language = stLanguage();
    $nonce = base64_encode(random_bytes(18));
    $umamiConfig = umamiConfiguration((string) stConfig('canonicalBaseUrl'));
    stSendHeaders($language, $nonce, $umamiConfig);

    $title = (string) stCopy("meta.{$page}Title", $language);
    $description = (string) stCopy("meta.{$page}Description", $language);
    $canonicalSuffix = match ($page) {
        'privacy' => trim((string) stConfig('privacyPath'), '/'),
        'support' => trim((string) stConfig('supportPath'), '/'),
        'terms' => trim((string) stConfig('termsPath'), '/'),
        default => '',
    };
    $canonical = stCanonical($canonicalSuffix);
    $route = stCurrentProductRoute();
    $otherLanguage = $language === 'es' ? 'en' : 'es';
    $structuredData = [
        '@context' => 'https://schema.org',
        '@type' => $page === 'landing' ? 'SoftwareApplication' : 'WebPage',
        'name' => $page === 'landing' ? stConfig('productName') : $title,
        'url' => $canonical,
        'description' => $description,
        'inLanguage' => $language,
    ];
    if ($page === 'landing') {
        $structuredData['applicationCategory'] = 'FinanceApplication';
        $structuredData['operatingSystem'] = implode(', ', (array) stConfig('supportedPlatforms'));
        $structuredData['publisher'] = ['@type' => 'Organization', 'name' => stConfig('publisherName')];
        $structuredData['creator'] = ['@type' => 'Person', 'name' => stConfig('creatorName')];
        $structuredData['image'] = stCanonical('assets/images/savetempo-og.png');
    }
    ?>
<!doctype html>
<html lang="<?= stEscape($language) ?>" data-page="<?= stEscape($page) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= stEscape($title) ?></title>
    <meta name="description" content="<?= stEscape($description) ?>">
    <meta name="author" content="Sergio Moreno">
    <meta name="robots" content="index, follow, max-image-preview:large">
    <meta name="theme-color" content="#09110e">
    <link rel="canonical" href="<?= stEscape($canonical) ?>">
    <link rel="alternate" hreflang="en" href="<?= stEscape($canonical) ?>?lang=en">
    <link rel="alternate" hreflang="es" href="<?= stEscape($canonical) ?>?lang=es">
    <link rel="alternate" hreflang="x-default" href="<?= stEscape($canonical) ?>">
    <link rel="icon" type="image/png" sizes="64x64" href="<?= stEscape(stAsset('favicon')) ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="<?= $language === 'es' ? 'es_ES' : 'en_US' ?>">
    <meta property="og:locale:alternate" content="<?= $language === 'es' ? 'en_US' : 'es_ES' ?>">
    <meta property="og:title" content="<?= stEscape($title) ?>">
    <meta property="og:description" content="<?= stEscape($description) ?>">
    <meta property="og:url" content="<?= stEscape($canonical) ?>">
    <meta property="og:image" content="<?= stEscape(stCanonical('assets/images/savetempo-og.png')) ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="SaveTempo — save at your own tempo">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="stylesheet" href="<?= stEscape(stPath('/assets/css/savetempo.css?v=1.0.1')) ?>">
    <script type="application/ld+json" nonce="<?= stEscape($nonce) ?>"><?= json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
    <?php umamiRenderTrackingScript($umamiConfig, "savetempo-{$page}-lang-{$language}"); ?>
    <script src="<?= stEscape(stPath('/assets/js/savetempo.js?v=1.0.1')) ?>" defer></script>
</head>
<body data-theme-label="<?= stEscape((string) stCopy('chrome.theme', $language)) ?>">
    <a class="skip-link" href="#main-content"><?= stEscape((string) stCopy('chrome.skip', $language)) ?></a>
    <header class="product-header" data-header>
        <div class="product-shell product-header-inner">
            <a class="product-brand" href="<?= stEscape(stPath('/')) ?>" aria-label="SaveTempo">
                <img src="<?= stEscape(stAsset('icon')) ?>" alt="" width="44" height="44" decoding="async">
                <span>SaveTempo</span>
            </a>
            <nav class="product-nav" aria-label="SaveTempo">
                <?php if ($page === 'landing'): ?>
                    <a href="#how-it-works"><?= stEscape((string) stCopy('chrome.how', $language)) ?></a>
                    <a href="#features"><?= stEscape((string) stCopy('chrome.features', $language)) ?></a>
                <?php else: ?>
                    <a href="<?= stEscape(stPath('/')) ?>"><?= stEscape((string) stCopy('chrome.backHome', $language)) ?></a>
                <?php endif; ?>
                <a href="<?= stEscape(stPath((string) stConfig('privacyPath'))) ?>"><?= stEscape((string) stCopy('chrome.privacy', $language)) ?></a>
            </nav>
            <div class="product-controls">
                <div class="language-switch" aria-label="<?= stEscape((string) stCopy('chrome.language', $language)) ?>">
                    <a href="<?= stEscape($route) ?>?lang=en" lang="en" hreflang="en" data-language="en"<?= $language === 'en' ? ' aria-current="page"' : '' ?>>EN</a>
                    <a href="<?= stEscape($route) ?>?lang=es" lang="es" hreflang="es" data-language="es"<?= $language === 'es' ? ' aria-current="page"' : '' ?>>ES</a>
                </div>
                <button class="theme-toggle" type="button" aria-label="<?= stEscape((string) stCopy('chrome.theme', $language)) ?>" data-theme-toggle><span aria-hidden="true">◐</span></button>
                <?php if (stStoreLinks() === []): ?>
                    <span class="header-status"><?= stEscape((string) stCopy('chrome.comingSoon', $language)) ?></span>
                <?php else: ?>
                    <a class="header-status header-download" href="#download"><?= stEscape((string) stCopy('chrome.features', $language)) ?></a>
                <?php endif; ?>
            </div>
        </div>
    </header>
    <main id="main-content">
        <?php $renderBody($language); ?>
    </main>
    <footer class="product-footer">
        <div class="product-shell footer-primary">
            <div>
                <a class="product-brand footer-brand" href="<?= stEscape(stPath('/')) ?>">
                    <img src="<?= stEscape(stAsset('icon')) ?>" alt="" width="48" height="48" loading="lazy" decoding="async">
                    <span>SaveTempo</span>
                </a>
                <p><?= stEscape((string) stCopy('chrome.publishedBy', $language)) ?></p>
            </div>
            <nav class="footer-nav" aria-label="SaveTempo footer">
                <a href="<?= stEscape(stPath((string) stConfig('privacyPath'))) ?>"><?= stEscape((string) stCopy('chrome.privacy', $language)) ?></a>
                <a href="<?= stEscape(stPath((string) stConfig('supportPath'))) ?>"><?= stEscape((string) stCopy('chrome.support', $language)) ?></a>
                <a href="<?= stEscape(stPath((string) stConfig('termsPath'))) ?>"><?= stEscape((string) stCopy('chrome.terms', $language)) ?></a>
            </nav>
            <?php stRenderStoreLinks('store-links footer-store-links'); ?>
        </div>
        <div class="product-shell footer-secondary">
            <p>© 2026 Sergiotech</p>
            <a href="<?= stEscape((string) stConfig('portfolioUrl')) ?>" target="_blank" rel="noopener noreferrer"><?= stEscape((string) stCopy('chrome.createdBy', $language)) ?> <span aria-hidden="true">↗</span></a>
        </div>
    </footer>
</body>
</html>
    <?php
}

function stRenderEmailText(string $text): void
{
    $email = (string) stConfig('supportEmail');
    $parts = explode($email, $text);
    foreach ($parts as $index => $part) {
        if ($index > 0) {
            echo '<a href="mailto:' . stEscape($email) . '">' . stEscape($email) . '</a>';
        }
        echo stEscape($part);
    }
}
