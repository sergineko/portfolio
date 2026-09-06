<?php
declare(strict_types=1);

/**
 * Render the SaveTempo portfolio card with one primary link and optional,
 * independent store links sourced from the central product configuration.
 *
 * @param callable(string): string $text
 */
function renderSaveTempoProjectCard(string $language, callable $text): void
{
    $escape = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    $storeLinks = stStoreLinks($language);
    $isAvailable = stConfig('releaseStatus') === 'available' && $storeLinks !== [];
    ?>
    <article class="project-showcase project-savetempo project-card--savetempo" data-reveal>
        <a class="project-card-overlay" href="<?= $escape(stPath('/')) ?>" aria-label="<?= $escape($text('view_savetempo')) ?>" data-umami-event="project-open" data-umami-event-project="savetempo"></a>
        <div class="project-copy">
            <div class="project-heading-row">
                <span class="project-index">01</span>
                <span class="project-status<?= $isAvailable ? '' : ' project-status-building' ?>"><i aria-hidden="true"></i> <?= $escape($text($isAvailable ? 'status_available' : 'status_coming_soon')) ?></span>
            </div>
            <p class="project-type"><?= $escape($text('savetempo_type')) ?></p>
            <h3>SaveTempo</h3>
            <p class="project-description"><?= $escape($text('savetempo_description')) ?></p>
            <ul class="project-tags" aria-label="<?= $escape($text('features_label')) ?>">
                <?php foreach ((array) stConfig('supportedPlatforms') as $platform): ?>
                    <li><?= $escape((string) $platform) ?></li>
                <?php endforeach; ?>
                <li><?= $escape($text('tag_local_first')) ?></li>
            </ul>
            <span class="project-link" aria-hidden="true"><?= $escape($text('view_savetempo')) ?> <i>↗</i></span>
            <?php if ($storeLinks !== []): ?>
                <div class="project-store-links">
                    <?php foreach ($storeLinks as $storeLink): ?>
                        <a class="project-store-link project-store-link--<?= $escape($storeLink['store']) ?>" href="<?= $escape($storeLink['url']) ?>" target="_blank" rel="noopener noreferrer">
                            <?php if ($storeLink['store'] === 'google'): ?>
                                <img class="google-play-badge" src="<?= $escape(stAsset('googlePlayBadge', $language)) ?>" alt="<?= $escape($storeLink['label']) ?>" width="646" height="250">
                            <?php else: ?>
                                <?= $escape($storeLink['label']) ?>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="project-app-preview project-card--savetempo__media">
            <img class="project-app-icon project-card--savetempo__icon" src="<?= $escape(stAsset('icon')) ?>" alt="" width="88" height="88" loading="lazy" decoding="async">
            <div class="project-phone project-phone-frame">
                <span aria-hidden="true"></span>
                <img class="project-phone-screenshot" src="<?= $escape(stAsset('today', $language)) ?>" alt="<?= $escape($text('savetempo_alt')) ?>" width="540" height="960" loading="lazy" decoding="async">
            </div>
        </div>
    </article>
    <?php
}
