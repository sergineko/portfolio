<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

define('PORTFOLIO_APP', true);
require_once dirname(__DIR__) . '/localization.php';

/**
 * @param mixed $actual
 * @param mixed $expected
 */
function assertSameValue(string $test, mixed $actual, mixed $expected): void
{
    if ($actual === $expected) {
        return;
    }

    fwrite(
        STDERR,
        sprintf(
            "%s: se esperaba %s y se obtuvo %s.\n",
            $test,
            var_export($expected, true),
            var_export($actual, true)
        )
    );
    exit(1);
}

$translations = portfolioTranslations();
assertSameValue(
    'Claves disponibles en inglés',
    array_keys(array_diff_key($translations['es'], $translations['en'])),
    []
);
assertSameValue(
    'Claves disponibles en español',
    array_keys(array_diff_key($translations['en'], $translations['es'])),
    []
);

$englishCopy = implode(' ', $translations['en']);
foreach (['Fibre', 'colour', 'specialisation', 'organisations', 'enquiry'] as $britishSpelling) {
    assertSameValue(
        "Inglés estadounidense sin {$britishSpelling}",
        str_contains($englishCopy, $britishSpelling),
        false
    );
}
assertSameValue(
    'Nombre estadounidense del puesto de fibra',
    $translations['en']['role_fiber'],
    'Fiber Optic Installer'
);

$browserCases = [
    ['es-ES,es;q=0.9,en;q=0.8', 'es'],
    ['en-GB,en;q=0.9,es;q=0.5', 'en'],
    ['fr-FR,es;q=0.8', 'en'],
    ['es;q=0.6,en;q=0.9', 'en'],
    ['*', null],
    ['', null],
];

foreach ($browserCases as [$header, $expected]) {
    assertSameValue(
        "Accept-Language {$header}",
        portfolioBrowserLanguage($header),
        $expected
    );
}

$_COOKIE = [];
unset(
    $_SERVER['HTTP_ACCEPT_LANGUAGE'],
    $_SERVER['HTTP_CF_IPCOUNTRY'],
    $_SERVER['HTTP_X_COUNTRY_CODE'],
    $_SERVER['GEOIP_COUNTRY_CODE']
);

$_SERVER['HTTP_CF_IPCOUNTRY'] = 'MX';
assertSameValue('País hispanohablante', portfolioDetectLanguage(), 'es');

$_SERVER['HTTP_CF_IPCOUNTRY'] = 'DE';
assertSameValue('País no hispanohablante', portfolioDetectLanguage(), 'en');

$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'es-ES';
assertSameValue('El navegador prevalece sobre el país', portfolioDetectLanguage(), 'es');

$_COOKIE['portfolio_lang'] = 'en';
assertSameValue('La elección manual prevalece', portfolioDetectLanguage(), 'en');

fwrite(STDOUT, "Pruebas de idioma superadas.\n");
