# SaveTempo microsite

## Architecture

SaveTempo is implemented as an independent product site mounted at `/apps/savetempo`. It does not use the portfolio header, navigation, form, theme code, content model or page state. The only integration point is the SaveTempo card in the portfolio Projects section, which reads the same central product configuration for its route and future Store links.

The product site is server-rendered PHP with standalone CSS and vanilla JavaScript. Its landing, guides, Privacy Policy, Support and Terms remain readable without JavaScript. JavaScript is limited to theme and language preferences, sticky-header state and progressive enhancement of the savings calculators.

```text
apps/savetempo/
├── config/product.php
├── content/translations.php
├── components/
│   ├── bootstrap.php
│   ├── guide-page.php
│   └── legal-page.php
├── assets/
│   ├── css/savetempo.css
│   ├── js/savetempo.js
│   └── images/
├── index.php
├── 52-week-savings-challenge/index.php
├── 365-day-savings-challenge/index.php
├── savings-challenge-app/index.php
├── privacy/index.php
├── support/index.php
└── terms/index.php
```

Public routes:

- `/apps/savetempo/`
- `/apps/savetempo/privacy/`
- `/apps/savetempo/support/`
- `/apps/savetempo/terms/`
- `/apps/savetempo/52-week-savings-challenge/`
- `/apps/savetempo/365-day-savings-challenge/`
- `/apps/savetempo/savings-challenge-app/`
- `/es/apps/savetempo/`
- `/es/apps/savetempo/privacy/`
- `/es/apps/savetempo/support/`
- `/es/apps/savetempo/terms/`
- `/es/apps/savetempo/reto-ahorro-52-semanas/`
- `/es/apps/savetempo/reto-ahorro-365-dias/`
- `/es/apps/savetempo/app-retos-ahorro/`

The legal routes are real directories with an `index.php`, so direct requests and refreshes do not depend on SPA rewrites.

## Product configuration

`apps/savetempo/config/product.php` is the single source for:

- slug, product name, publisher and creator;
- support and privacy email;
- localized base paths, canonical URLs and page paths;
- Google Play and App Store URLs;
- release status and supported platforms;
- locale list and fixed policy dates;
- portable asset references;
- website privacy behavior.

The current Store values reflect the Android release:

```php
'googlePlayUrl' => 'https://play.google.com/store/apps/details?id=es.sergiotech.savetempo',
'appStoreUrl' => null,
'releaseStatus' => 'available',
```

`stStoreLinks()` returns only valid configured URLs. The portfolio card and SaveTempo calls to action display the official Google Play badge; no App Store control is rendered while its URL remains unconfigured.

## Content and language

All public product copy lives in `apps/savetempo/content/translations.php` and `apps/savetempo/content/guides.php`, with professional English and Spanish versions. English uses `/apps/savetempo/.../` and Spanish uses `/es/apps/savetempo/.../`. Legacy `?lang=en` and `?lang=es` requests are permanently redirected to the equivalent clean URL. Requests without an explicit language path default to English.

The optional browser preference is stored in `localStorage` under `savetempo-language`; no language cookie or account is created. Main legal content is server-rendered and never relies on this storage or on JavaScript.

## Branding and asset provenance

The web copies are derived from the approved SaveTempo release assets. The Flutter project was not modified.

| Web asset | SaveTempo source |
| --- | --- |
| `savetempo-icon.webp`, `savetempo-favicon.png` | `docs/release/store/brand/source/savetempo-app-icon-1024.png` |
| `today-{en,es}.webp` | `docs/release/store/screenshots/android/{locale}/01-today.png` |
| `plans-{en,es}.webp` | `docs/release/store/screenshots/android/{locale}/02-saving-plans.png` |
| `plan-detail-{en,es}.webp` | `docs/release/store/screenshots/android/{locale}/04-plan-progress.png` |
| `calendar-{en,es}.webp` | `docs/release/store/screenshots/android/{locale}/05-global-calendar.png` |
| `reminders-{en,es}.webp` | `docs/release/store/screenshots/android/{locale}/06-saving-reminders.png` |
| `settings-{en,es}.webp` | `docs/release/store/screenshots/android/{locale}/07-local-settings.png` |
| `savetempo-og.png` | Deterministic composition using the approved icon and the real English Home screenshot |
| `google-play-badge-{en,es}.png` | Official localized Google Play badge artwork |

Screenshots were resized from 1080×1920 to 540×960 and encoded as WebP without changing the original files. The landing uses CSS device frames rather than third-party mockups.

## Website privacy

The portfolio and SaveTempo pages do not embed analytics scripts or interaction tracking. The portfolio contact form keeps its functional session and CSRF protection. Each portfolio URL now has a fixed language; the ES/EN selector links to those canonical URLs.

SaveTempo PHP pages do not start a session or set cookies. Because the microsite shares the `sergiotech.es` origin, a browser that previously visited the portfolio can still attach its path-wide first-party session or language cookie; SaveTempo does not read or use it. Language and theme choices can be stored in browser local storage (`savetempo-language` and `savetempo-theme`). The hosting provider may process technical request logs for website operation and security. Website behavior is disclosed separately from mobile-app behavior in the public Privacy Policy.

The app statements come from SaveTempo's `PROJECT_STATUS.md`, `docs/PERSISTENCE.md`, `docs/NOTIFICATIONS.md`, `docs/SETTINGS.md`, `docs/release/store_compliance.md` and security audit: current production code has no backend, account, cloud sync, analytics, ads or banking integration; Android app backup/transfer is disabled; final iOS backup treatment remains subject to release validation.

## SEO and accessibility

Each route provides a localized title and description, canonical URL, reciprocal hreflang links, Open Graph metadata, Twitter card and JSON-LD. Guide pages add Article structured data, server-rendered useful content and interactive savings tables. `sitemap.xml` contains the seven English/Spanish route pairs (14 SaveTempo URLs), and `robots.txt` allows them.

The product site uses semantic landmarks and headings, a skip link, visible keyboard focus, descriptive screenshot alternatives, native details/summary controls, sufficient text contrast, touch-sized controls and a `prefers-reduced-motion` fallback. No interaction depends only on hover.

## Performance

There are no new runtime dependencies, external fonts, frameworks, video, carousel or animation library. The hero image has explicit dimensions and high fetch priority. Below-the-fold screenshots have explicit dimensions, lazy loading and async decoding. Optimized product images total well under one megabyte.

## Coolify and direct routes

The existing Coolify/Nixpacks PHP flow remains unchanged: repository root, PHP 8.1+, port 80, no build or custom start command. The added routes are ordinary PHP directory indexes and are compatible with Nginx or Apache directory-index behavior. No secret, environment-variable requirement or server action was added.

Optional deployment variables exist only for portability:

```dotenv
SAVETEMPO_BASE_PATH=/apps/savetempo
SAVETEMPO_CANONICAL_BASE_URL=https://sergiotech.es/apps/savetempo
```

They are not required for the current deployment because safe defaults are committed.

## Adding another Sergiotech product

1. Create a sibling directory under `apps/<slug>/`.
2. Give the product its own config, content, header/footer, CSS and assets.
3. Reuse only small infrastructure patterns that fit, such as server-side locale detection, safe URL helpers and Store-link filtering.
4. Add one coherent project card and one structured-data entry to the portfolio.
5. Add public routes to the sitemap and tests.

The next product is not required to copy SaveTempo's visual identity, glass treatment or content structure.

# Moving SaveTempo to its own domain

1. Move the complete `apps/savetempo/` folder to the new PHP site's public root, preserving `config`, `content`, `components` and `assets` together.
2. Change `basePath` in `config/product.php` from `/apps/savetempo` to `/`, or set `SAVETEMPO_BASE_PATH=/`.
3. Change `canonicalBaseUrl` to the final origin, for example `https://savetempo.app`, or set `SAVETEMPO_CANONICAL_BASE_URL`.
4. Keep legal and guide route directories, and verify their directory-index handling on the new host.
5. Update Store URLs only when each platform is published. All Store links update together.
6. Copy the product-specific sitemap entries into the new domain's sitemap and update their origins. Add a domain-level `robots.txt` that allows landing and legal routes.
7. Deploy with any PHP 8.1+ host, container or static-compatible PHP platform that serves directory indexes and HTTPS. No database or Node build is required.
8. Verify asset paths, canonical/OG URLs, both languages, direct legal routes and HTTPS on the final host.
9. After the new domain is verified, add permanent path-preserving redirects from `sergiotech.es/apps/savetempo/*` to the equivalent new-domain path. For example, `/apps/savetempo/privacy` should redirect to `https://savetempo.app/privacy`. Do not enable these redirects before the new site is ready.
10. Keep or update the Sergiotech project card to point to the new configured product URL.

## Legal and release note

**LEGAL REVIEW RECOMMENDED BEFORE PUBLIC STORE SUBMISSION.**

The public pages avoid inventing a company form, address, tax identifier, phone number or governing jurisdiction. Final legal review, the iOS backup decision/validation, production signing and Store submission remain outside this web task.

## Web QA artifacts

Requested browser screenshots are stored under `docs/products/savetempo/web-qa/`. They are website QA evidence, not Store screenshots.
