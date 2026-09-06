<?php
declare(strict_types=1);

/**
 * SaveTempo product-site configuration.
 *
 * This file is the only place that should change when the microsite moves to
 * its own domain or when an official store listing becomes available.
 */
return [
    'slug' => 'savetempo',
    'productName' => 'SaveTempo',
    'publisherName' => 'Sergiotech',
    'creatorName' => 'Sergio Moreno',
    'supportEmail' => 'smorgarc@sergiotech.es',
    'privacyEmail' => 'smorgarc@sergiotech.es',
    'basePath' => rtrim(getenv('SAVETEMPO_BASE_PATH') ?: '/apps/savetempo', '/') ?: '/',
    'canonicalBaseUrl' => rtrim(
        getenv('SAVETEMPO_CANONICAL_BASE_URL') ?: 'https://sergiotech.es/apps/savetempo',
        '/'
    ),
    'privacyPath' => '/privacy',
    'supportPath' => '/support',
    'termsPath' => '/terms',
    'googlePlayUrl' => 'https://play.google.com/store/apps/details?id=es.sergiotech.savetempo',
    'appStoreUrl' => null,
    'releaseStatus' => 'comingSoon',
    'supportedPlatforms' => ['Android', 'iOS'],
    'locales' => ['en', 'es'],
    'defaultLocale' => 'en',
    'privacyLastUpdated' => '2026-08-12',
    'termsLastUpdated' => '2026-08-12',
    'portfolioUrl' => 'https://sergiotech.es/',
    'assets' => [
        'icon' => '/assets/images/savetempo-icon.webp',
        'favicon' => '/assets/images/savetempo-favicon.png',
        'ogImage' => '/assets/images/savetempo-og.png',
        'today' => ['en' => '/assets/images/today-en.webp', 'es' => '/assets/images/today-es.webp'],
        'plans' => ['en' => '/assets/images/plans-en.webp', 'es' => '/assets/images/plans-es.webp'],
        'planDetail' => ['en' => '/assets/images/plan-detail-en.webp', 'es' => '/assets/images/plan-detail-es.webp'],
        'calendar' => ['en' => '/assets/images/calendar-en.webp', 'es' => '/assets/images/calendar-es.webp'],
        'reminders' => ['en' => '/assets/images/reminders-en.webp', 'es' => '/assets/images/reminders-es.webp'],
        'settings' => ['en' => '/assets/images/settings-en.webp', 'es' => '/assets/images/settings-es.webp'],
    ],
    'websitePrivacy' => [
        'analyticsEnabled' => true,
        'analyticsProvider' => 'Umami',
        'cookiesUsed' => false,
        'localPreferences' => ['language', 'theme'],
    ],
];
