<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

function stRenderLegalPage(string $page): void
{
    stRenderPage($page, static function (string $language) use ($page): void {
        $legal = stCopy('legal', $language);
        $eyebrowKey = $page . 'Eyebrow';
        $titleKey = $page . 'Title';
        $introKey = $page . 'Intro';
        $sectionsKey = $page === 'support' ? 'supportFaq' : $page . 'Sections';
        $sections = is_array($legal) ? ($legal[$sectionsKey] ?? []) : [];
        ?>
        <article class="legal-page product-shell">
            <header class="legal-hero">
                <p class="section-kicker"><?= stEscape((string) ($legal[$eyebrowKey] ?? '')) ?></p>
                <h1><?= stEscape((string) ($legal[$titleKey] ?? '')) ?></h1>
                <p class="legal-intro"><?= stEscape((string) ($legal[$introKey] ?? '')) ?></p>
                <?php if ($page !== 'support'): ?>
                    <p class="last-updated"><?= stEscape((string) ($legal['lastUpdated'] ?? '')) ?></p>
                <?php endif; ?>
            </header>

            <?php if ($page === 'support'): ?>
                <section class="support-contact" aria-labelledby="support-email-title">
                    <p class="section-kicker">SaveTempo</p>
                    <h2 id="support-email-title"><?= stEscape((string) ($legal['emailTitle'] ?? '')) ?></h2>
                    <p><?= stEscape((string) ($legal['emailBody'] ?? '')) ?></p>
                    <a class="product-button product-button-primary" href="mailto:<?= stEscape((string) stConfig('supportEmail')) ?>"><?= stEscape((string) ($legal['emailCta'] ?? '')) ?> <span aria-hidden="true">↗</span></a>
                </section>
                <div class="legal-sections support-faq">
                    <?php foreach ((array) $sections as $index => $section): ?>
                        <details<?= $index === 0 ? ' open' : '' ?>>
                            <summary><?= stEscape((string) ($section[0] ?? '')) ?></summary>
                            <p><?= stEscape((string) ($section[1] ?? '')) ?></p>
                        </details>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="legal-sections">
                    <?php foreach ((array) $sections as $section): ?>
                        <section>
                            <h2><?= stEscape((string) ($section[0] ?? '')) ?></h2>
                            <?php foreach ((array) ($section[1] ?? []) as $paragraph): ?>
                                <p><?php stRenderEmailText((string) $paragraph); ?></p>
                            <?php endforeach; ?>
                        </section>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </article>
        <?php
    });
}
