<?php
declare(strict_types=1);

require_once __DIR__ . '/components/bootstrap.php';

stRenderPage('landing', static function (string $language): void {
    $landing = stCopy('landing', $language);
    $copy = static fn(string $key): string => (string) ($landing[$key] ?? $key);
    ?>
    <section class="hero product-shell" aria-labelledby="hero-title">
        <div class="hero-copy">
            <p class="section-kicker"><?= stEscape($copy('eyebrow')) ?></p>
            <h1 id="hero-title"><?= stEscape($copy('heroTitle')) ?></h1>
            <p class="hero-lede"><?= stEscape($copy('heroBody')) ?></p>
            <div class="hero-actions" id="download">
                <?php if (stStoreLinks() === []): ?>
                    <span class="availability-pill"><i aria-hidden="true"></i><?= stEscape((string) stCopy('chrome.availableSoon', $language)) ?></span>
                <?php else: ?>
                    <?php stRenderStoreLinks(); ?>
                <?php endif; ?>
                <a class="product-button product-button-secondary" href="#how-it-works"><?= stEscape($copy('heroCta')) ?> <span aria-hidden="true">↓</span></a>
            </div>
            <p class="hero-note"><?= stEscape($copy('heroNote')) ?></p>
        </div>
        <div class="hero-visual" aria-label="SaveTempo app preview">
            <div class="ambient-orb ambient-orb-one" aria-hidden="true"></div>
            <div class="ambient-orb ambient-orb-two" aria-hidden="true"></div>
            <div class="hero-brand-chip">
                <img src="<?= stEscape(stAsset('icon')) ?>" alt="" width="56" height="56" decoding="async">
                <span><strong>SaveTempo</strong><?= stEscape($copy('moneyTitle')) ?></span>
            </div>
            <figure class="phone phone-hero product-phone-frame">
                <div class="phone-speaker" aria-hidden="true"></div>
                <img class="product-phone-screenshot" src="<?= stEscape(stAsset('today')) ?>" alt="<?= stEscape($copy('heroAlt')) ?>" width="540" height="960" fetchpriority="high" decoding="async">
            </figure>
            <div class="tempo-card" aria-hidden="true">
                <span>YOUR TEMPO</span>
                <strong>8 / 10</strong>
                <i><b></b></i>
            </div>
        </div>
    </section>

    <section class="idea-section product-shell" aria-labelledby="idea-title">
        <div class="editorial-heading">
            <p class="section-kicker"><?= stEscape($copy('ideaKicker')) ?></p>
            <h2 id="idea-title"><?= stEscape($copy('ideaTitle')) ?></h2>
        </div>
        <div class="idea-copy">
            <p><?= stEscape($copy('ideaBody')) ?></p>
            <aside class="money-statement">
                <span aria-hidden="true">↘</span>
                <h3><?= stEscape($copy('moneyTitle')) ?></h3>
                <p><?= stEscape($copy('moneyBody')) ?></p>
            </aside>
        </div>
    </section>

    <section class="how-section" id="how-it-works" aria-labelledby="how-title">
        <div class="product-shell">
            <div class="section-heading-wide">
                <p class="section-kicker"><?= stEscape($copy('howKicker')) ?></p>
                <h2 id="how-title"><?= stEscape($copy('howTitle')) ?></h2>
            </div>
            <ol class="steps">
                <li>
                    <span>01</span>
                    <div><h3><?= stEscape($copy('step1Title')) ?></h3><p><?= stEscape($copy('step1Body')) ?></p></div>
                </li>
                <li>
                    <span>02</span>
                    <div><h3><?= stEscape($copy('step2Title')) ?></h3><p><?= stEscape($copy('step2Body')) ?></p></div>
                </li>
                <li>
                    <span>03</span>
                    <div><h3><?= stEscape($copy('step3Title')) ?></h3><p><?= stEscape($copy('step3Body')) ?></p></div>
                </li>
            </ol>
        </div>
    </section>

    <div id="features">
        <section class="feature feature-today product-shell" aria-labelledby="today-title">
            <div class="feature-copy">
                <p class="section-kicker"><?= stEscape($copy('todayKicker')) ?></p>
                <h2 id="today-title"><?= stEscape($copy('todayTitle')) ?></h2>
                <p><?= stEscape($copy('todayBody')) ?></p>
                <ul class="inline-facts" aria-label="SaveTempo Home">
                    <li>Today's Saving</li><li>Pending savings</li><li>Your Tempo</li><li>Active Plans</li>
                </ul>
            </div>
            <div class="feature-visual offset-phone-stage">
                <figure class="phone phone-feature product-phone-frame">
                    <div class="phone-speaker" aria-hidden="true"></div>
                    <img class="product-phone-screenshot" src="<?= stEscape(stAsset('today')) ?>" alt="<?= stEscape($copy('todayAlt')) ?>" width="540" height="960" loading="lazy" decoding="async">
                </figure>
                <div class="pulse-note" aria-hidden="true"><span>Today</span><strong>Clear. Concrete. Yours.</strong></div>
            </div>
        </section>

        <section class="feature-plans" aria-labelledby="plans-title">
            <div class="product-shell feature-plans-grid">
                <div class="plans-visual">
                    <figure class="phone phone-feature phone-plans product-phone-frame">
                        <div class="phone-speaker" aria-hidden="true"></div>
                        <img class="product-phone-screenshot" src="<?= stEscape(stAsset('plans')) ?>" alt="<?= stEscape($copy('plansAlt')) ?>" width="540" height="960" loading="lazy" decoding="async">
                    </figure>
                    <div class="plan-label plan-label-week"><span>52</span><p><?= stEscape($copy('weekPlan')) ?><small><?= stEscape($copy('weekMeta')) ?></small></p></div>
                    <div class="plan-label plan-label-day"><span>365</span><p><?= stEscape($copy('dayPlan')) ?><small><?= stEscape($copy('dayMeta')) ?></small></p></div>
                </div>
                <div class="feature-copy">
                    <p class="section-kicker"><?= stEscape($copy('plansKicker')) ?></p>
                    <h2 id="plans-title"><?= stEscape($copy('plansTitle')) ?></h2>
                    <p><?= stEscape($copy('plansBody')) ?></p>
                    <div class="pattern-line" aria-label="Saving patterns"><span>Increasing</span><i></i><span>Random</span></div>
                </div>
            </div>
        </section>

        <section class="progress-section product-shell" aria-labelledby="progress-title">
            <div class="section-heading-wide progress-heading">
                <div>
                    <p class="section-kicker"><?= stEscape($copy('progressKicker')) ?></p>
                    <h2 id="progress-title"><?= stEscape($copy('progressTitle')) ?></h2>
                </div>
                <p><?= stEscape($copy('progressBody')) ?></p>
            </div>
            <div class="dual-phones">
                <figure class="phone phone-feature phone-detail product-phone-frame">
                    <div class="phone-speaker" aria-hidden="true"></div>
                    <img class="product-phone-screenshot" src="<?= stEscape(stAsset('planDetail')) ?>" alt="<?= stEscape($copy('detailAlt')) ?>" width="540" height="960" loading="lazy" decoding="async">
                </figure>
                <figure class="phone phone-feature phone-calendar product-phone-frame">
                    <div class="phone-speaker" aria-hidden="true"></div>
                    <img class="product-phone-screenshot" src="<?= stEscape(stAsset('calendar')) ?>" alt="<?= stEscape($copy('calendarAlt')) ?>" width="540" height="960" loading="lazy" decoding="async">
                </figure>
                <div class="calendar-legend" aria-label="Calendar statuses">
                    <span><i class="saved"></i>Saved</span>
                    <span><i class="partial"></i>Partial</span>
                    <span><i class="pending"></i>Pending</span>
                    <span><i class="upcoming"></i>Upcoming</span>
                </div>
            </div>
        </section>

        <section class="reminders-section" aria-labelledby="reminders-title">
            <div class="product-shell reminders-grid">
                <div class="feature-copy reminders-copy">
                    <p class="section-kicker"><?= stEscape($copy('remindersKicker')) ?></p>
                    <h2 id="reminders-title"><?= stEscape($copy('remindersTitle')) ?></h2>
                    <p><?= stEscape($copy('remindersBody')) ?></p>
                    <div class="notification-sample" aria-hidden="true">
                        <img src="<?= stEscape(stAsset('icon')) ?>" alt="" width="40" height="40" loading="lazy">
                        <p><strong>SaveTempo</strong><span>Your next contribution is ready.</span></p>
                        <small>now</small>
                    </div>
                </div>
                <figure class="phone phone-feature phone-reminders product-phone-frame">
                    <div class="phone-speaker" aria-hidden="true"></div>
                    <img class="product-phone-screenshot" src="<?= stEscape(stAsset('reminders')) ?>" alt="<?= stEscape($copy('remindersAlt')) ?>" width="540" height="960" loading="lazy" decoding="async">
                </figure>
            </div>
        </section>
    </div>

    <section class="privacy-section product-shell" aria-labelledby="privacy-title">
        <div class="privacy-mark" aria-hidden="true">
            <span></span><span></span><span></span>
            <b>LOCAL</b>
        </div>
        <div class="feature-copy">
            <p class="section-kicker"><?= stEscape($copy('privacyKicker')) ?></p>
            <h2 id="privacy-title"><?= stEscape($copy('privacyTitle')) ?></h2>
            <p><?= stEscape($copy('privacyBody')) ?></p>
            <ul class="privacy-facts">
                <?php foreach ((array) ($landing['privacyFacts'] ?? []) as $fact): ?><li><span aria-hidden="true">✓</span><?= stEscape((string) $fact) ?></li><?php endforeach; ?>
            </ul>
            <a class="text-link" href="<?= stEscape(stPath((string) stConfig('privacyPath'))) ?>"><?= stEscape($copy('privacyCta')) ?> <span aria-hidden="true">↗</span></a>
        </div>
    </section>

    <section class="showcase-section" aria-labelledby="showcase-title">
        <div class="product-shell">
            <div class="section-heading-wide showcase-heading">
                <p class="section-kicker"><?= stEscape($copy('showcaseKicker')) ?></p>
                <h2 id="showcase-title"><?= stEscape($copy('showcaseTitle')) ?></h2>
                <p><?= stEscape($copy('showcaseBody')) ?></p>
            </div>
            <div class="showcase-track" tabindex="0" aria-label="<?= stEscape($copy('showcaseKicker')) ?>" data-showcase-track>
                <?php
                $showcase = [
                    ['today', $copy('todayAlt'), "Today's Saving", 'today'],
                    ['plans', $copy('plansAlt'), $copy('plansKicker'), 'plans'],
                    ['planDetail', $copy('detailAlt'), 'Plan Detail', 'detail'],
                    ['calendar', $copy('calendarAlt'), $copy('progressKicker'), 'calendar'],
                    ['settings', $language === 'es' ? 'Ajustes de SaveTempo con preferencias locales.' : 'SaveTempo Settings with local preferences.', 'Settings', 'settings'],
                ];
                foreach ($showcase as $item):
                ?>
                    <figure class="showcase-item showcase-item--<?= stEscape($item[3]) ?>">
                        <div class="showcase-phone product-phone-frame"><img class="product-phone-screenshot" src="<?= stEscape(stAsset($item[0])) ?>" alt="<?= stEscape($item[1]) ?>" width="540" height="960" loading="lazy" decoding="async"></div>
                        <figcaption><?= stEscape($item[2]) ?></figcaption>
                    </figure>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="faq-section product-shell" aria-labelledby="faq-title">
        <div class="faq-heading">
            <p class="section-kicker"><?= stEscape($copy('faqKicker')) ?></p>
            <h2 id="faq-title"><?= stEscape($copy('faqTitle')) ?></h2>
        </div>
        <div class="faq-list">
            <?php foreach ((array) ($landing['faq'] ?? []) as $index => $faq): ?>
                <details<?= $index === 0 ? ' open' : '' ?>>
                    <summary><?= stEscape((string) ($faq[0] ?? '')) ?></summary>
                    <p><?= stEscape((string) ($faq[1] ?? '')) ?></p>
                </details>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="final-cta product-shell" aria-labelledby="final-title">
        <div>
            <img src="<?= stEscape(stAsset('icon')) ?>" alt="" width="80" height="80" loading="lazy" decoding="async">
            <p class="section-kicker">SaveTempo</p>
            <h2 id="final-title"><?= stEscape($copy('finalTitle')) ?></h2>
            <p><?= stEscape($copy('finalBody')) ?></p>
            <?php if (stStoreLinks() === []): ?>
                <span class="availability-pill"><i aria-hidden="true"></i><?= stEscape((string) stCopy('chrome.availableSoon', $language)) ?></span>
            <?php else: ?>
                <?php stRenderStoreLinks(); ?>
            <?php endif; ?>
            <a class="product-button product-button-secondary" href="#features"><?= stEscape($copy('finalCta')) ?> <span aria-hidden="true">↑</span></a>
        </div>
    </section>
    <?php
});
