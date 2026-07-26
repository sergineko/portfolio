<?php
declare(strict_types=1);

define('PORTFOLIO_APP', true);
require_once __DIR__ . '/localization.php';

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';

session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'cookie_secure' => $isHttps,
    'use_strict_mode' => true,
]);

$requestedLanguage = portfolioSupportedLanguage($_GET['lang'] ?? null);
if ($requestedLanguage !== null) {
    portfolioSetLanguageCookie($requestedLanguage, $isHttps);
    $language = $requestedLanguage;
} else {
    $language = portfolioDetectLanguage();
}

$t = static fn(string $key): string => portfolioText($language, $key);
$escape = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
$siteUrl = rtrim(getenv('PORTFOLIO_SITE_URL') ?: 'https://sergiotech.es', '/');

$umamiScriptUrl = trim((string) (getenv('UMAMI_SCRIPT_URL') ?: ''));
$umamiWebsiteId = trim((string) (getenv('UMAMI_WEBSITE_ID') ?: ''));
$umamiUrlParts = $umamiScriptUrl !== '' ? parse_url($umamiScriptUrl) : false;
$umamiOrigin = '';
$umamiEnabled =
    is_array($umamiUrlParts) &&
    strtolower((string) ($umamiUrlParts['scheme'] ?? '')) === 'https' &&
    isset($umamiUrlParts['host']) &&
    !isset($umamiUrlParts['user']) &&
    !isset($umamiUrlParts['pass']) &&
    filter_var($umamiScriptUrl, FILTER_VALIDATE_URL) !== false &&
    preg_match('/^[0-9a-f]{8}(?:-[0-9a-f]{4}){3}-[0-9a-f]{12}$/i', $umamiWebsiteId) === 1;

if ($umamiEnabled) {
    $umamiOrigin = 'https://' . strtolower((string) $umamiUrlParts['host']);
    if (isset($umamiUrlParts['port'])) {
        $umamiOrigin .= ':' . (int) $umamiUrlParts['port'];
    }
}

$siteHost = strtolower((string) (parse_url($siteUrl, PHP_URL_HOST) ?: 'sergiotech.es'));
$umamiDomainCandidates = preg_split(
    '/\s*,\s*/',
    trim((string) (getenv('UMAMI_DOMAINS') ?: $siteHost)),
    -1,
    PREG_SPLIT_NO_EMPTY
) ?: [];
$umamiDomains = [];
foreach ($umamiDomainCandidates as $domainCandidate) {
    $domainCandidate = strtolower(trim($domainCandidate));
    if (filter_var($domainCandidate, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) !== false) {
        $umamiDomains[] = $domainCandidate;
    }
}
$umamiDomains = implode(',', array_values(array_unique($umamiDomains ?: [$siteHost])));

$nonce = base64_encode(random_bytes(18));
$stylesheetPath = __DIR__ . '/assets/css/styles.css';
$inlineStyles = is_readable($stylesheetPath) ? file_get_contents($stylesheetPath) : false;
$inlineStyles = is_string($inlineStyles) ? $inlineStyles : '';

$scriptSources = "'self' 'nonce-{$nonce}'";
$connectSources = "'self'";
if ($umamiEnabled) {
    $scriptSources .= " {$umamiOrigin}";
    $connectSources .= " {$umamiOrigin}";
}

$contentSecurityPolicy =
    "default-src 'self'; " .
    "base-uri 'self'; connect-src {$connectSources}; font-src 'self'; form-action 'self'; " .
    "frame-ancestors 'none'; img-src 'self' data:; object-src 'none'; " .
    "script-src {$scriptSources}; style-src 'self' 'nonce-{$nonce}'";

if ($isHttps) {
    $contentSecurityPolicy .= '; upgrade-insecure-requests';
}

header("Content-Security-Policy: {$contentSecurityPolicy}");
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Content-Type-Options: nosniff');
header("Content-Language: {$language}");
header('Vary: Accept-Language, Cookie, CF-IPCountry, X-Country-Code, GeoIP-Country-Code', false);

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$_SESSION['form_started_at'] = time();

$birthDate = new DateTimeImmutable('1993-09-15');
$today = new DateTimeImmutable('today');
$age = $birthDate->diff($today)->y;
$localizedUrl = "{$siteUrl}/?lang={$language}";

$status = filter_input(INPUT_GET, 'status', FILTER_UNSAFE_RAW);
$formMessage = match ($status) {
    'success' => $t('form_success'),
    'error' => $t('form_redirect_error'),
    default => '',
};

$personId = "{$siteUrl}/#person";
$portfolioWebsiteId = "{$siteUrl}/#website";
$profilePageId = "{$localizedUrl}#profile-page";
$myWorkingAreaId = 'https://myworkingarea.com/#website';
$vyrseaId = 'https://vyrsea.com/#website';

$structuredData = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'ProfilePage',
            '@id' => $profilePageId,
            'url' => $localizedUrl,
            'name' => $t('seo_title'),
            'description' => $t('seo_description'),
            'inLanguage' => $language,
            'mainEntity' => ['@id' => $personId],
            'isPartOf' => ['@id' => $portfolioWebsiteId],
            'mentions' => [
                ['@id' => $myWorkingAreaId],
                ['@id' => $vyrseaId],
            ],
        ],
        [
            '@type' => 'WebSite',
            '@id' => $portfolioWebsiteId,
            'url' => "{$siteUrl}/",
            'name' => 'Sergio Moreno García',
            'inLanguage' => ['es', 'en'],
            'publisher' => ['@id' => $personId],
        ],
        [
            '@type' => 'Person',
            '@id' => $personId,
            'name' => 'Sergio Moreno García',
            'url' => "{$siteUrl}/",
            'image' => "{$siteUrl}/assets/images/sergio-moreno-portrait-800.webp",
            'email' => 'mailto:smorgarc@sergiotech.es',
            'telephone' => '+34614839879',
            'mainEntityOfPage' => ['@id' => $profilePageId],
            'homeLocation' => [
                '@type' => 'Place',
                'name' => $t('location'),
            ],
            'jobTitle' => $t('schema_job_title'),
            'knowsAbout' => [
                $t('schema_backend'),
                $t('schema_fullstack'),
                $t('schema_integrations'),
                $t('schema_web_apps'),
            ],
        ],
        [
            '@type' => 'WebSite',
            '@id' => $myWorkingAreaId,
            'url' => 'https://myworkingarea.com/',
            'name' => 'My Working Area',
            'description' => $t('mwa_description'),
            'creator' => ['@id' => $personId],
        ],
        [
            '@type' => 'WebSite',
            '@id' => $vyrseaId,
            'url' => 'https://vyrsea.com/',
            'name' => 'Vyrsea',
            'description' => $t('vyrsea_description'),
            'creator' => ['@id' => $personId],
        ],
    ],
];
?>
<!doctype html>
<html lang="<?= $escape($language) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $escape($t('seo_title')) ?></title>
    <meta name="description" content="<?= $escape($t('seo_description')) ?>">
    <meta name="author" content="Sergio Moreno García">
    <meta name="robots" content="index, follow, max-image-preview:large">
    <meta name="theme-color" content="#0a0d10">
    <meta name="apple-mobile-web-app-title" content="Sergio Moreno">
    <link rel="canonical" href="<?= $escape($localizedUrl) ?>">
    <link rel="alternate" hreflang="es" href="<?= $escape($siteUrl) ?>/?lang=es">
    <link rel="alternate" hreflang="en" href="<?= $escape($siteUrl) ?>/?lang=en">
    <link rel="alternate" hreflang="x-default" href="<?= $escape($siteUrl) ?>/">
    <link rel="icon" type="image/png" sizes="192x192" href="/favicon-192x192.png">
    <link rel="shortcut icon" href="/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/icons/apple-touch-icon-v2.png">
    <link rel="manifest" href="/site.webmanifest">

    <meta property="og:type" content="profile">
    <meta property="og:locale" content="<?= $language === 'es' ? 'es_ES' : 'en_US' ?>">
    <meta property="og:locale:alternate" content="<?= $language === 'es' ? 'en_US' : 'es_ES' ?>">
    <meta property="og:title" content="<?= $escape($t('seo_title')) ?>">
    <meta property="og:description" content="<?= $escape($t('og_description')) ?>">
    <meta property="og:url" content="<?= $escape($localizedUrl) ?>">
    <meta property="profile:first_name" content="Sergio">
    <meta property="profile:last_name" content="Moreno García">
    <meta name="twitter:card" content="summary">

    <?php if ($inlineStyles !== ''): ?>
        <style nonce="<?= $escape($nonce) ?>"><?= $inlineStyles ?></style>
    <?php else: ?>
        <link rel="stylesheet" href="assets/css/styles.css?v=2.3.0">
    <?php endif; ?>
    <script type="application/ld+json" nonce="<?= $escape($nonce) ?>">
        <?= json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>
    </script>
    <?php if ($umamiEnabled): ?>
        <script
            defer
            src="<?= $escape($umamiScriptUrl) ?>"
            data-website-id="<?= $escape($umamiWebsiteId) ?>"
            data-domains="<?= $escape($umamiDomains) ?>"
            data-do-not-track="true"
            data-exclude-search="true"
            data-tag="lang-<?= $escape($language) ?>"></script>
    <?php endif; ?>
    <script src="assets/js/main.js?v=2.2.0" defer></script>
</head>
<body
    data-theme-dark="<?= $escape($t('theme_enable_dark')) ?>"
    data-theme-light="<?= $escape($t('theme_enable_light')) ?>"
    data-menu-open="<?= $escape($t('menu_open')) ?>"
    data-menu-close="<?= $escape($t('menu_close')) ?>">
    <a class="skip-link" href="#contenido"><?= $escape($t('skip_content')) ?></a>

    <header class="site-header" data-header>
        <div class="container header-inner">
            <a class="brand" href="#inicio" aria-label="<?= $escape($t('go_home')) ?>">
                <img class="brand-mark" src="assets/icons/sergio-tech-mark-42.webp" srcset="assets/icons/sergio-tech-mark-42.webp 1x, assets/icons/sergio-tech-mark-84.webp 2x" alt="" width="42" height="42" aria-hidden="true" decoding="async">
                <span class="brand-name">Sergio Moreno</span>
            </a>

            <nav class="main-nav" id="main-navigation" aria-label="<?= $escape($t('main_navigation')) ?>" data-nav>
                <a href="#sobre-mi"><?= $escape($t('nav_about')) ?></a>
                <a href="#experiencia"><?= $escape($t('nav_experience')) ?></a>
                <a href="#proyectos"><?= $escape($t('nav_projects')) ?></a>
                <a href="#contacto"><?= $escape($t('nav_contact')) ?></a>
            </nav>

            <div class="header-actions">
                <nav class="language-switcher" aria-label="<?= $escape($t('language_selector')) ?>">
                    <a href="?lang=es" lang="es" hreflang="es" aria-label="<?= $escape($t('language_spanish')) ?>" data-umami-event="language-switch" data-umami-event-language="es"<?= $language === 'es' ? ' aria-current="page"' : '' ?>>ES</a>
                    <a href="?lang=en" lang="en" hreflang="en" aria-label="<?= $escape($t('language_english')) ?>" data-umami-event="language-switch" data-umami-event-language="en"<?= $language === 'en' ? ' aria-current="page"' : '' ?>>EN</a>
                </nav>
                <button class="theme-toggle" type="button" aria-label="<?= $escape($t('theme_change')) ?>" title="<?= $escape($t('theme_title')) ?>" data-theme-toggle>
                    <span class="theme-icon" aria-hidden="true"></span>
                </button>
                <button class="menu-toggle" type="button" aria-controls="main-navigation" aria-expanded="false" aria-label="<?= $escape($t('menu_open')) ?>" data-menu-toggle>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </header>

    <main id="contenido">
        <section class="hero section" id="inicio" aria-labelledby="hero-title">
            <div class="hero-glow hero-glow-one" aria-hidden="true"></div>
            <div class="hero-glow hero-glow-two" aria-hidden="true"></div>
            <div class="container hero-grid">
                <div class="hero-copy">
                    <p class="eyebrow">
                        <span class="status-dot" aria-hidden="true"></span>
                        <?= $escape($t('hero_eyebrow')) ?>
                    </p>
                    <h1 id="hero-title"><?= $escape($t('hero_title')) ?> <span><?= $escape($t('hero_title_highlight')) ?></span></h1>
                    <p class="hero-lead"><?= $escape($t('hero_lead')) ?></p>
                    <div class="hero-actions">
                        <a class="button button-primary" href="#proyectos" data-umami-event="projects-cta"><?= $escape($t('hero_projects')) ?> <span aria-hidden="true">↘</span></a>
                        <a class="button button-secondary" href="#contacto" data-umami-event="contact-cta"><?= $escape($t('hero_contact')) ?></a>
                    </div>
                    <div class="hero-focus" aria-label="<?= $escape($t('focus_label')) ?>">
                        <span>Backend</span>
                        <span><?= $escape($t('focus_integrations')) ?></span>
                        <span><?= $escape($t('focus_performance')) ?></span>
                    </div>
                </div>

                <aside class="hero-portrait" aria-label="<?= $escape($t('portrait_label')) ?>">
                    <div class="portrait-halo" aria-hidden="true"></div>
                    <div class="portrait-frame">
                        <picture>
                            <source
                                type="image/avif"
                                srcset="assets/images/sergio-moreno-portrait-480.avif 480w, assets/images/sergio-moreno-portrait-640.avif 640w, assets/images/sergio-moreno-portrait-800.avif 800w"
                                sizes="(max-width: 440px) calc(100vw - 4rem), (max-width: 540px) calc(100vw - 6rem), (max-width: 900px) 432px, 464px">
                            <source
                                type="image/webp"
                                srcset="assets/images/sergio-moreno-portrait-480.webp 480w, assets/images/sergio-moreno-portrait-640.webp 640w, assets/images/sergio-moreno-portrait-800.webp 800w"
                                sizes="(max-width: 440px) calc(100vw - 4rem), (max-width: 540px) calc(100vw - 6rem), (max-width: 900px) 432px, 464px">
                            <img
                                src="assets/images/sergio-moreno-portrait-640.webp"
                                srcset="assets/images/sergio-moreno-portrait-480.webp 480w, assets/images/sergio-moreno-portrait-640.webp 640w, assets/images/sergio-moreno-portrait-800.webp 800w"
                                sizes="(max-width: 440px) calc(100vw - 4rem), (max-width: 540px) calc(100vw - 6rem), (max-width: 900px) 432px, 464px"
                                alt="<?= $escape($t('portrait_alt')) ?>"
                                width="800"
                                height="1000"
                                loading="eager"
                                decoding="async"
                                fetchpriority="high">
                        </picture>
                        <div class="portrait-caption">
                            <div>
                                <strong>Sergio Moreno García</strong>
                                <span><?= $escape($t('backend_developer')) ?></span>
                            </div>
                            <span class="portrait-location">Murcia · ES</span>
                        </div>
                    </div>
                    <div class="portrait-chip portrait-chip-top" aria-hidden="true">
                        <span>●</span> Backend · Full-stack
                    </div>
                    <div class="portrait-chip portrait-chip-bottom" aria-hidden="true">
                        <small><?= $escape($t('portrait_experience')) ?></small>
                        <strong><?= $escape($t('years_9')) ?></strong>
                    </div>
                </aside>
            </div>
            <div class="container hero-metrics" aria-label="<?= $escape($t('highlights_label')) ?>">
                <div>
                    <strong>2014</strong>
                    <span><?= $escape($t('professional_start')) ?></span>
                </div>
                <div>
                    <strong><?= $escape($t('years_9')) ?></strong>
                    <span><?= $escape($t('in_development')) ?></span>
                </div>
                <div>
                    <strong><?= $escape($t('sectors_2')) ?></strong>
                    <span><?= $escape($t('banking_telecom')) ?></span>
                </div>
            </div>
        </section>

        <div class="expertise-strip" aria-label="<?= $escape($t('expertise_label')) ?>">
            <div class="expertise-track">
                <span>Backend</span><i aria-hidden="true">✦</i>
                <span><?= $escape($t('expertise_architecture')) ?></span><i aria-hidden="true">✦</i>
                <span><?= $escape($t('expertise_integrations')) ?></span><i aria-hidden="true">✦</i>
                <span><?= $escape($t('expertise_digital_product')) ?></span><i aria-hidden="true">✦</i>
                <span><?= $escape($t('expertise_performance')) ?></span><i aria-hidden="true">✦</i>
                <span><?= $escape($t('expertise_reliability')) ?></span><i aria-hidden="true">✦</i>
            </div>
        </div>

        <section class="section about" id="sobre-mi" aria-labelledby="about-title">
            <div class="container section-grid">
                <div class="section-heading" data-reveal>
                    <p class="section-number"><?= $escape($t('about_number')) ?></p>
                    <h2 id="about-title"><?= $escape($t('about_title')) ?></h2>
                </div>
                <div class="about-content" data-reveal>
                    <p class="large-copy"><?= $escape($t('about_age_before')) ?> <strong data-age data-birthdate="1993-09-15"><?= $age ?></strong> <?= $escape($t('about_age_after')) ?></p>
                    <p><?= $escape($t('about_body')) ?></p>

                    <div class="capabilities" aria-label="<?= $escape($t('capabilities_label')) ?>">
                        <article>
                            <span>01</span>
                            <h3>Backend</h3>
                            <p><?= $escape($t('capability_backend')) ?></p>
                        </article>
                        <article>
                            <span>02</span>
                            <h3>Full-stack</h3>
                            <p><?= $escape($t('capability_fullstack')) ?></p>
                        </article>
                        <article>
                            <span>03</span>
                            <h3><?= $escape($t('capability_critical_title')) ?></h3>
                            <p><?= $escape($t('capability_critical')) ?></p>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <section class="section experience" id="experiencia" aria-labelledby="experience-title">
            <div class="container">
                <div class="section-heading split-heading" data-reveal>
                    <div>
                        <p class="section-number"><?= $escape($t('experience_number')) ?></p>
                        <h2 id="experience-title"><?= $escape($t('experience_title')) ?></h2>
                    </div>
                    <p><?= $escape($t('experience_intro')) ?></p>
                </div>

                <ol class="timeline">
                    <li class="timeline-item" data-reveal>
                        <div class="timeline-period">
                            <time datetime="2023-09-04">04.09.2023</time>
                            <span>—</span>
                            <time datetime="2026-01-12">12.01.2026</time>
                        </div>
                        <div class="timeline-role">
                            <h3><?= $escape($t('role_backend')) ?></h3>
                            <p>CaixaBank</p>
                        </div>
                        <span class="timeline-index">05</span>
                    </li>
                    <li class="timeline-item" data-reveal>
                        <div class="timeline-period">
                            <time datetime="2023-01-18">18.01.2023</time>
                            <span>—</span>
                            <time datetime="2023-08-30">30.08.2023</time>
                        </div>
                        <div class="timeline-role">
                            <h3><?= $escape($t('role_backend')) ?></h3>
                            <p>Banca March</p>
                        </div>
                        <span class="timeline-index">04</span>
                    </li>
                    <li class="timeline-item" data-reveal>
                        <div class="timeline-period">
                            <time datetime="2017-02-27">27.02.2017</time>
                            <span>—</span>
                            <time datetime="2023-01-18">18.01.2023</time>
                        </div>
                        <div class="timeline-role">
                            <h3><?= $escape($t('role_backend')) ?></h3>
                            <p><?= $escape($t('telefonica_unit')) ?></p>
                        </div>
                        <span class="timeline-index">03</span>
                    </li>
                    <li class="timeline-item" data-reveal>
                        <div class="timeline-period">
                            <time datetime="2016-10-06">06.10.2016</time>
                            <span>—</span>
                            <time datetime="2017-02-14">14.02.2017</time>
                        </div>
                        <div class="timeline-role">
                            <h3><?= $escape($t('role_fullstack')) ?></h3>
                            <p>Alfatec Sistemas, S.L.</p>
                        </div>
                        <span class="timeline-index">02</span>
                    </li>
                    <li class="timeline-item" data-reveal>
                        <div class="timeline-period">
                            <time datetime="2014-06-26">26.06.2014</time>
                            <span>—</span>
                            <time datetime="2016-10-05">05.10.2016</time>
                        </div>
                        <div class="timeline-role">
                            <h3><?= $escape($t('role_fiber')) ?></h3>
                            <p>Telecanal 2, S.L.</p>
                        </div>
                        <span class="timeline-index">01</span>
                    </li>
                </ol>
            </div>
        </section>

        <section class="section projects" id="proyectos" aria-labelledby="projects-title">
            <div class="container">
                <div class="section-heading split-heading" data-reveal>
                    <div>
                        <p class="section-number"><?= $escape($t('projects_number')) ?></p>
                        <h2 id="projects-title"><?= $escape($t('projects_title')) ?></h2>
                    </div>
                    <p><?= $escape($t('projects_intro')) ?></p>
                </div>

                <div class="project-list">
                    <a class="project-showcase project-mwa" href="https://myworkingarea.com/" target="_blank" rel="noopener" aria-label="<?= $escape($t('mwa_link_label')) ?>" data-umami-event="project-open" data-umami-event-project="myworkingarea" data-reveal>
                        <div class="project-copy">
                            <div class="project-heading-row">
                                <span class="project-index">01</span>
                                <span class="project-status"><i aria-hidden="true"></i> <?= $escape($t('status_available')) ?></span>
                            </div>
                            <p class="project-type"><?= $escape($t('mwa_type')) ?></p>
                            <h3>My Working Area</h3>
                            <p class="project-description"><?= $escape($t('mwa_description')) ?></p>
                            <ul class="project-tags" aria-label="<?= $escape($t('features_label')) ?>">
                                <li><?= $escape($t('tag_digital_product')) ?></li>
                                <li><?= $escape($t('tag_web_tools')) ?></li>
                                <li><?= $escape($t('tag_production')) ?></li>
                            </ul>
                            <span class="project-link"><?= $escape($t('explore_project')) ?> <i aria-hidden="true">↗</i></span>
                        </div>
                        <div class="project-browser" aria-hidden="true">
                            <div class="browser-bar">
                                <span></span><span></span><span></span>
                                <p>myworkingarea.com</p>
                            </div>
                            <div class="project-screenshot">
                                <img src="assets/images/myworkingarea-website.webp" alt="<?= $escape($t('mwa_alt')) ?>" width="1280" height="800" loading="lazy" decoding="async">
                            </div>
                        </div>
                    </a>

                    <a class="project-showcase project-vyrsea" href="https://vyrsea.com/" target="_blank" rel="noopener" aria-label="<?= $escape($t('vyrsea_link_label')) ?>" data-umami-event="project-open" data-umami-event-project="vyrsea" data-reveal>
                        <div class="project-copy">
                            <div class="project-heading-row">
                                <span class="project-index">02</span>
                                <span class="project-status project-status-building"><i aria-hidden="true"></i> <?= $escape($t('status_building')) ?></span>
                            </div>
                            <p class="project-type"><?= $escape($t('vyrsea_type')) ?></p>
                            <h3>Vyrsea</h3>
                            <p class="project-description"><?= $escape($t('vyrsea_description')) ?></p>
                            <ul class="project-tags" aria-label="<?= $escape($t('features_label')) ?>">
                                <li>CMS</li>
                                <li><?= $escape($t('tag_bookings')) ?></li>
                                <li><?= $escape($t('tag_soon')) ?></li>
                            </ul>
                            <span class="project-link"><?= $escape($t('discover_vyrsea')) ?> <i aria-hidden="true">↗</i></span>
                        </div>
                        <div class="project-browser" aria-hidden="true">
                            <div class="browser-bar">
                                <span></span><span></span><span></span>
                                <p>vyrsea.com</p>
                            </div>
                            <div class="project-screenshot">
                                <img src="assets/images/vyrsea-website.webp" alt="<?= $escape($t('vyrsea_alt')) ?>" width="1280" height="800" loading="lazy" decoding="async">
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </section>

        <section class="section contact" id="contacto" aria-labelledby="contact-title">
            <div class="container contact-grid">
                <div class="contact-intro" data-reveal>
                    <p class="section-number"><?= $escape($t('contact_number')) ?></p>
                    <h2 id="contact-title"><?= $escape($t('contact_title')) ?></h2>
                    <p><?= $escape($t('contact_intro')) ?></p>

                    <div class="direct-contact">
                        <!--email_off-->
                        <a href="mailto:smorgarc@sergiotech.es" data-umami-event="contact-link" data-umami-event-method="email">
                            <span><?= $escape($t('contact_email')) ?></span>
                            <strong>smorgarc@sergiotech.es</strong>
                        </a>
                        <!--/email_off-->
                        <a href="tel:+34614839879" data-umami-event="contact-link" data-umami-event-method="phone">
                            <span><?= $escape($t('contact_phone')) ?></span>
                            <strong>+34 614 839 879</strong>
                        </a>
                        <div>
                            <span><?= $escape($t('contact_location')) ?></span>
                            <strong><?= $escape($t('location')) ?></strong>
                        </div>
                    </div>
                </div>

                <form
                    class="contact-form"
                    action="contact.php"
                    method="post"
                    data-contact-form
                    data-reveal
                    data-sending="<?= $escape($t('form_sending')) ?>"
                    data-submit-label="<?= $escape($t('form_submit')) ?>"
                    data-generic-error="<?= $escape($t('form_generic_error')) ?>"
                    data-network-error="<?= $escape($t('form_network_error')) ?>">
                    <input type="hidden" name="csrf_token" value="<?= $escape($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="language" value="<?= $escape($language) ?>">
                    <div class="honeypot" aria-hidden="true">
                        <label for="website"><?= $escape($t('honeypot')) ?></label>
                        <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="form-row">
                        <div class="field">
                            <label for="name"><?= $escape($t('form_name')) ?> <span aria-hidden="true">*</span></label>
                            <input id="name" name="name" type="text" autocomplete="name" maxlength="80" required placeholder="<?= $escape($t('form_name_placeholder')) ?>">
                        </div>
                        <div class="field">
                            <label for="email"><?= $escape($t('form_email')) ?> <span aria-hidden="true">*</span></label>
                            <input id="email" name="email" type="email" autocomplete="email" inputmode="email" maxlength="120" required placeholder="tu@email.com">
                        </div>
                    </div>

                    <div class="field">
                        <label for="subject"><?= $escape($t('form_subject')) ?> <span aria-hidden="true">*</span></label>
                        <input id="subject" name="subject" type="text" maxlength="120" required placeholder="<?= $escape($t('form_subject_placeholder')) ?>">
                    </div>

                    <div class="field">
                        <label for="message"><?= $escape($t('form_message')) ?> <span aria-hidden="true">*</span></label>
                        <textarea id="message" name="message" rows="6" minlength="20" maxlength="5000" required placeholder="<?= $escape($t('form_message_placeholder')) ?>"></textarea>
                        <small><span data-character-count>0</span> / 5000</small>
                    </div>

                    <label class="consent">
                        <input name="privacy" type="checkbox" value="accepted" required>
                        <span><?= $escape($t('form_consent')) ?></span>
                    </label>

                    <button class="button button-primary submit-button" type="submit">
                        <span data-submit-label><?= $escape($t('form_submit')) ?></span>
                        <span aria-hidden="true">↗</span>
                    </button>

                    <p class="form-status<?= $formMessage !== '' ? ' is-visible ' . ($status === 'success' ? 'is-success' : 'is-error') : '' ?>" role="status" aria-live="polite" data-form-status><?= $escape($formMessage) ?></p>
                </form>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container footer-inner">
            <a class="brand" href="#inicio" aria-label="<?= $escape($t('footer_home')) ?>">
                <img class="brand-mark" src="assets/icons/sergio-tech-mark-42.webp" srcset="assets/icons/sergio-tech-mark-42.webp 1x, assets/icons/sergio-tech-mark-84.webp 2x" alt="" width="42" height="42" aria-hidden="true" decoding="async">
                <span class="brand-name">Sergio Moreno García</span>
            </a>
            <p><?= $escape($t('footer_note')) ?></p>
            <p>© <span data-current-year><?= date('Y') ?></span> Sergio Moreno García</p>
        </div>
    </footer>
</body>
</html>
