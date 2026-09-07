<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

/** @var array<string, array<string, array<string, mixed>>> $saveTempoGuides */
$saveTempoGuides = require __DIR__ . '/../content/guides.php';

function stGuideMoney(float $amount, string $language): string
{
    return number_format($amount, 2, $language === 'es' ? ',' : '.', $language === 'es' ? '.' : ',') . ' €';
}

function stRenderGuidePage(string $page): void
{
    global $saveTempoGuides;

    stRenderPage($page, static function (string $language) use ($page, $saveTempoGuides): void {
        $guide = $saveTempoGuides[$language][$page] ?? $saveTempoGuides['en'][$page] ?? [];
        $copy = static fn(string $key): string => (string) ($guide[$key] ?? $key);
        ?>
        <article class="guide-page">
            <header class="guide-hero product-shell">
                <nav class="breadcrumbs" aria-label="<?= $language === 'es' ? 'Migas de pan' : 'Breadcrumb' ?>">
                    <a href="<?= stEscape(stRoutePath('landing', $language)) ?>">SaveTempo</a><span aria-hidden="true">/</span><span><?= stEscape($copy('kicker')) ?></span>
                </nav>
                <p class="section-kicker"><?= stEscape($copy('kicker')) ?></p>
                <h1><?= stEscape($copy('title')) ?></h1>
                <p class="guide-lede"><?= stEscape($copy('intro')) ?></p>
                <?php stRenderStoreLinks(); ?>
            </header>

            <?php if ($page === 'week52' || $page === 'day365'): ?>
                <?php
                $isWeekly = $page === 'week52';
                $step = $isWeekly ? 1.0 : 0.01;
                $factor = $isWeekly ? 1378 : 66795;
                ?>
                <section class="challenge-calculator product-shell" aria-labelledby="calculator-title" data-challenge-calculator data-locale="<?= stEscape($language) ?>" data-currency="EUR">
                    <div class="calculator-summary">
                        <div>
                            <label for="challenge-step"><?= stEscape($copy('inputLabel')) ?></label>
                            <span class="money-input"><input id="challenge-step" type="number" min="0.01" step="0.01" value="<?= number_format($step, 2, '.', '') ?>" inputmode="decimal" data-challenge-step><b>€</b></span>
                        </div>
                        <div><span><?= stEscape($copy('totalLabel')) ?></span><strong data-challenge-total data-factor="<?= $factor ?>"><?= stEscape(stGuideMoney($step * $factor, $language)) ?></strong></div>
                        <div><span><?= stEscape($copy('frequencyLabel')) ?></span><strong><?= stEscape($copy('frequencyValue')) ?></strong></div>
                    </div>
                    <div class="challenge-table-heading">
                        <h2 id="calculator-title"><?= stEscape($copy('tableTitle')) ?></h2>
                        <p><?= stEscape($copy('tableIntro')) ?></p>
                    </div>
                    <div class="challenge-table-wrap" tabindex="0">
                        <table class="challenge-table">
                            <thead><tr><?php foreach ((array) ($guide['headers'] ?? []) as $header): ?><th scope="col"><?= stEscape((string) $header) ?></th><?php endforeach; ?></tr></thead>
                            <tbody>
                            <?php if ($isWeekly): ?>
                                <?php $running = 0.0; for ($week = 1; $week <= 52; $week++): $amount = $step * $week; $running += $amount; ?>
                                    <tr><th scope="row"><?= $week ?></th><td data-challenge-amount data-index="<?= $week ?>"><?= stEscape(stGuideMoney($amount, $language)) ?></td><td data-challenge-running data-index="<?= (int) ($week * ($week + 1) / 2) ?>"><?= stEscape(stGuideMoney($running, $language)) ?></td></tr>
                                <?php endfor; ?>
                            <?php else: ?>
                                <?php $days = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31]; $day = 0; foreach ($days as $index => $monthDays): $day += $monthDays; ?>
                                    <tr><th scope="row"><?= stEscape((string) (($guide['months'][$index] ?? $index + 1))) ?></th><td><?= $day ?></td><td data-challenge-running data-index="<?= (int) ($day * ($day + 1) / 2) ?>"><?= stEscape(stGuideMoney($step * $day * ($day + 1) / 2, $language)) ?></td></tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            <?php elseif (!empty($guide['features'])): ?>
                <section class="guide-features product-shell" aria-label="<?= stEscape($copy('kicker')) ?>">
                    <?php foreach ((array) $guide['features'] as $index => $feature): ?>
                        <section><span><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span><h2><?= stEscape((string) ($feature[0] ?? '')) ?></h2><p><?= stEscape((string) ($feature[1] ?? '')) ?></p></section>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>

            <div class="guide-sections product-shell">
                <?php foreach ((array) ($guide['sections'] ?? []) as $section): ?>
                    <section><h2><?= stEscape((string) ($section[0] ?? '')) ?></h2><p><?= stEscape((string) ($section[1] ?? '')) ?></p></section>
                <?php endforeach; ?>
            </div>

            <nav class="guide-related product-shell" aria-label="<?= $language === 'es' ? 'Guías relacionadas' : 'Related guides' ?>">
                <a href="<?= stEscape(stRoutePath('week52', $language)) ?>"><?= $language === 'es' ? 'Reto de 52 semanas' : '52 week challenge' ?></a>
                <a href="<?= stEscape(stRoutePath('day365', $language)) ?>"><?= $language === 'es' ? 'Reto de 365 días' : '365 day challenge' ?></a>
                <a href="<?= stEscape(stRoutePath('challengeApp', $language)) ?>"><?= $language === 'es' ? 'App de retos de ahorro' : 'Savings challenge app' ?></a>
            </nav>

            <section class="guide-cta product-shell">
                <div><p class="section-kicker">SaveTempo</p><h2><?= stEscape($copy('ctaTitle')) ?></h2><p><?= stEscape($copy('ctaBody')) ?></p><?php stRenderStoreLinks(); ?></div>
                <img src="<?= stEscape(stAsset('plans', $language)) ?>" alt="<?= stEscape((string) stCopy('landing.plansAlt', $language)) ?>" width="540" height="960" loading="lazy" decoding="async">
            </section>
        </article>
        <?php
    });
}
