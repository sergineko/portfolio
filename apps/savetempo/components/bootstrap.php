<?php
declare(strict_types=1);

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
    $value = $_GET['lang'] ?? '';
    $requested = is_string($value) ? strtolower(trim($value)) : '';

    if (is_array($supported) && in_array($requested, $supported, true)) {
        return $requested;
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
    $suffix = '/' . ltrim($suffix, '/');
    return $suffix === '/' ? $base . '/' : $base . $suffix;
}

function stLocalizedBase(string $type, string $language): string
{
    $values = (array) stConfig($type);
    $fallback = $type === 'localizedCanonicalBaseUrls'
        ? (string) stConfig('canonicalBaseUrl')
        : (string) stConfig('basePath');

    return rtrim((string) ($values[$language] ?? $fallback), '/');
}

function stRouteSuffix(string $page, string $language): string
{
    $pagePaths = (array) stConfig('pagePaths');
    $localizedPaths = (array) ($pagePaths[$page] ?? []);
    $suffix = (string) ($localizedPaths[$language] ?? $localizedPaths[(string) stConfig('defaultLocale')] ?? '/');

    return '/' . ltrim($suffix, '/');
}

function stRoutePath(string $page, string $language): string
{
    $base = stLocalizedBase('localizedBasePaths', $language);
    $suffix = stRouteSuffix($page, $language);

    return $base . ($suffix === '/' ? '/' : $suffix);
}

function stRouteCanonical(string $page, string $language): string
{
    $base = stLocalizedBase('localizedCanonicalBaseUrls', $language);
    $suffix = stRouteSuffix($page, $language);

    return $base . ($suffix === '/' ? '/' : $suffix);
}

function stRedirectLegacyRequest(string $page, string $language): void
{
    if (PHP_SAPI === 'cli' || !in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true)) {
        return;
    }

    $uri = (string) ($_SERVER['REQUEST_URI'] ?? stRoutePath($page, $language));
    $path = parse_url($uri, PHP_URL_PATH);
    $query = [];
    parse_str((string) (parse_url($uri, PHP_URL_QUERY) ?? ''), $query);
    $canonical = stRouteCanonical($page, $language);
    $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
    $canonicalHost = (string) parse_url($canonical, PHP_URL_HOST);

    // PHP also normalizes legacy links when Apache rewrite rules are unavailable.
    if ($path !== stRoutePath($page, $language)
        || array_key_exists('lang', $query)
        || $host === "www.{$canonicalHost}"
    ) {
        unset($query['lang']);
        $queryString = http_build_query($query);
        header('Location: ' . $canonical . ($queryString === '' ? '' : '?' . $queryString), true, 301);
        exit;
    }
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

function stContentSecurityPolicy(string $nonce, bool $isHttps): string
{
    $policy = "default-src 'self'; base-uri 'self'; connect-src 'self'; font-src 'self'; "
        . "form-action 'none'; frame-ancestors 'none'; img-src 'self' data:; object-src 'none'; "
        . "script-src 'self' 'nonce-{$nonce}'; style-src 'self'";
    if ($isHttps) {
        $policy .= '; upgrade-insecure-requests';
    }

    return $policy;
}

function stSendHeaders(string $language, string $nonce): void
{
    if (headers_sent()) {
        return;
    }

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
    $csp = stContentSecurityPolicy($nonce, $isHttps);

    header("Content-Security-Policy: {$csp}");
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-Content-Type-Options: nosniff');
    header('Permissions-Policy: camera=(), geolocation=(), microphone=(), payment=(), usb=()');
    header("Content-Language: {$language}");
    header('Vary: Accept-Language', false);
}

function stRenderStoreLinks(string $className = 'store-links'): void
{
    $language = stLanguage();
    $links = stStoreLinks();
    if ($links === []) {
        return;
    }
    echo '<div class="' . stEscape($className) . '">';
    foreach ($links as $link) {
        echo '<a class="store-link store-link-' . stEscape($link['store']) . '" href="' . stEscape($link['url']) . '" target="_blank" rel="noopener noreferrer">';
        if ($link['store'] === 'google') {
            echo '<img class="google-play-badge" src="' . stEscape(stAsset('googlePlayBadge', $language)) . '" alt="' . stEscape($link['label']) . '" width="646" height="250">';
        } else {
            echo '<span aria-hidden="true">●</span>' . stEscape($link['label']);
        }
        echo '</a>';
    }
    echo '</div>';
}

function stRenderPage(string $page, callable $renderBody): void
{
    $language = stLanguage();
    stRedirectLegacyRequest($page, $language);
    $nonce = base64_encode(random_bytes(18));
    stSendHeaders($language, $nonce);

    $title = (string) stCopy("meta.{$page}Title", $language);
    $description = (string) stCopy("meta.{$page}Description", $language);
    $canonical = stRouteCanonical($page, $language);
    $englishCanonical = stRouteCanonical($page, 'en');
    $spanishCanonical = stRouteCanonical($page, 'es');
    $englishRoute = stRoutePath($page, 'en');
    $spanishRoute = stRoutePath($page, 'es');
    $isGuide = in_array($page, ['week52', 'day365', 'challengeApp'], true);
    $structuredData = [
        '@context' => 'https://schema.org',
        '@type' => $page === 'landing' ? 'SoftwareApplication' : ($isGuide ? 'Article' : 'WebPage'),
        'name' => $page === 'landing' ? stConfig('productName') : $title,
        'url' => $canonical,
        'description' => $description,
        'inLanguage' => $language,
        'dateModified' => $page === 'privacy' ? stConfig('privacyLastUpdated') : '2026-09-06',
    ];
    if ($page === 'landing') {
        $googlePlayUrl = stConfig('googlePlayUrl');
        $structuredData['@id'] = stCanonical() . '#software';
        $structuredData['applicationCategory'] = 'FinanceApplication';
        $structuredData['operatingSystem'] = implode(', ', (array) stConfig('supportedPlatforms'));
        $structuredData['isAccessibleForFree'] = true;
        $structuredData['publisher'] = ['@type' => 'Organization', 'name' => stConfig('publisherName'), 'url' => stConfig('portfolioUrl')];
        $structuredData['creator'] = ['@type' => 'Person', 'name' => stConfig('creatorName'), 'url' => stConfig('portfolioUrl')];
        $structuredData['image'] = stCanonical('assets/images/savetempo-og.png');
        if (is_string($googlePlayUrl) && filter_var($googlePlayUrl, FILTER_VALIDATE_URL)) {
            $structuredData['downloadUrl'] = $googlePlayUrl;
            $structuredData['sameAs'] = $googlePlayUrl;
            $structuredData['offers'] = [
                '@type' => 'Offer',
                'price' => '0',
                'priceCurrency' => 'EUR',
                'availability' => 'https://schema.org/InStock',
                'url' => $googlePlayUrl,
            ];
        }
    } elseif ($isGuide) {
        $structuredData['datePublished'] = '2026-09-06';
        $structuredData['headline'] = $title;
        $structuredData['mainEntityOfPage'] = $canonical;
        $structuredData['author'] = ['@type' => 'Person', 'name' => stConfig('creatorName'), 'url' => stConfig('portfolioUrl')];
        $structuredData['publisher'] = ['@type' => 'Organization', 'name' => stConfig('publisherName'), 'url' => stConfig('portfolioUrl')];
        $structuredData['image'] = stCanonical('assets/images/savetempo-og.png');
    }
    $storeLinks = stStoreLinks($language);
    ?>
<!doctype html>
<html lang="<?= stEscape($language) ?>" data-page="<?= stEscape($page) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= stEscape($title) ?></title>
    <meta name="description" content="<?= stEscape($description) ?>">
    <meta name="author" content="Sergio Moreno">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="theme-color" content="#09110e">
    <link rel="canonical" href="<?= stEscape($canonical) ?>">
    <link rel="alternate" hreflang="en" href="<?= stEscape($englishCanonical) ?>">
    <link rel="alternate" hreflang="es" href="<?= stEscape($spanishCanonical) ?>">
    <link rel="alternate" hreflang="x-default" href="<?= stEscape($englishCanonical) ?>">
    <link rel="icon" type="image/png" sizes="64x64" href="<?= stEscape(stAsset('favicon')) ?>">
    <meta property="og:type" content="<?= $isGuide ? 'article' : 'website' ?>">
    <meta property="og:locale" content="<?= $language === 'es' ? 'es_ES' : 'en_US' ?>">
    <meta property="og:locale:alternate" content="<?= $language === 'es' ? 'en_US' : 'es_ES' ?>">
    <meta property="og:title" content="<?= stEscape($title) ?>">
    <meta property="og:description" content="<?= stEscape($description) ?>">
    <meta property="og:url" content="<?= stEscape($canonical) ?>">
    <meta property="og:image" content="<?= stEscape(stCanonical('assets/images/savetempo-og.png')) ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="SaveTempo — save at your own tempo">
    <meta property="og:site_name" content="SaveTempo">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="stylesheet" href="<?= stEscape(stPath('/assets/css/savetempo.css?v=1.0.3')) ?>">
    <script type="application/ld+json" nonce="<?= stEscape($nonce) ?>"><?= json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
    <script src="<?= stEscape(stPath('/assets/js/savetempo.js?v=1.0.2')) ?>" defer></script>
</head>
<body data-theme-label="<?= stEscape((string) stCopy('chrome.theme', $language)) ?>">
    <a class="skip-link" href="#main-content"><?= stEscape((string) stCopy('chrome.skip', $language)) ?></a>
    <header class="product-header" data-header>
        <div class="product-shell product-header-inner">
            <a class="product-brand" href="<?= stEscape(stRoutePath('landing', $language)) ?>" aria-label="SaveTempo">
                <img src="<?= stEscape(stAsset('icon')) ?>" alt="" width="44" height="44" decoding="async">
                <span>SaveTempo</span>
            </a>
            <nav class="product-nav" aria-label="SaveTempo">
                <?php if ($page === 'landing'): ?>
                    <a href="#how-it-works"><?= stEscape((string) stCopy('chrome.how', $language)) ?></a>
                    <a href="#features"><?= stEscape((string) stCopy('chrome.features', $language)) ?></a>
                <?php else: ?>
                    <a href="<?= stEscape(stRoutePath('landing', $language)) ?>"><?= stEscape((string) stCopy('chrome.backHome', $language)) ?></a>
                <?php endif; ?>
                <a href="<?= stEscape(stRoutePath('privacy', $language)) ?>"><?= stEscape((string) stCopy('chrome.privacy', $language)) ?></a>
            </nav>
            <div class="product-controls">
                <div class="language-switch" aria-label="<?= stEscape((string) stCopy('chrome.language', $language)) ?>">
                    <a href="<?= stEscape($englishRoute) ?>" lang="en" hreflang="en" data-language="en"<?= $language === 'en' ? ' aria-current="page"' : '' ?>>EN</a>
                    <a href="<?= stEscape($spanishRoute) ?>" lang="es" hreflang="es" data-language="es"<?= $language === 'es' ? ' aria-current="page"' : '' ?>>ES</a>
                </div>
                <button class="theme-toggle" type="button" aria-label="<?= stEscape((string) stCopy('chrome.theme', $language)) ?>" data-theme-toggle><span aria-hidden="true">◐</span></button>
                <?php if ($storeLinks === []): ?>
                    <span class="header-status"><?= stEscape((string) stCopy('chrome.comingSoon', $language)) ?></span>
                <?php else: ?>
                    <a class="header-status header-download" href="<?= stEscape($storeLinks[0]['url']) ?>" target="_blank" rel="noopener noreferrer"><?= stEscape((string) stCopy('chrome.download', $language)) ?></a>
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
                <a class="product-brand footer-brand" href="<?= stEscape(stRoutePath('landing', $language)) ?>">
                    <img src="<?= stEscape(stAsset('icon')) ?>" alt="" width="48" height="48" loading="lazy" decoding="async">
                    <span>SaveTempo</span>
                </a>
                <p><?= stEscape((string) stCopy('chrome.publishedBy', $language)) ?></p>
            </div>
            <nav class="footer-nav" aria-label="<?= stEscape((string) stCopy('chrome.footerNav', $language)) ?>">
                <a href="<?= stEscape(stRoutePath('privacy', $language)) ?>"><?= stEscape((string) stCopy('chrome.privacy', $language)) ?></a>
                <a href="<?= stEscape(stRoutePath('support', $language)) ?>"><?= stEscape((string) stCopy('chrome.support', $language)) ?></a>
                <a href="<?= stEscape(stRoutePath('terms', $language)) ?>"><?= stEscape((string) stCopy('chrome.terms', $language)) ?></a>
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
