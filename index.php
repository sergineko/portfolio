<?php
declare(strict_types=1);

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';

session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'cookie_secure' => $isHttps,
    'use_strict_mode' => true,
]);

$nonce = base64_encode(random_bytes(18));
$stylesheetPath = __DIR__ . '/assets/css/styles.css';
$inlineStyles = is_readable($stylesheetPath) ? file_get_contents($stylesheetPath) : false;
$inlineStyles = is_string($inlineStyles) ? $inlineStyles : '';

$contentSecurityPolicy =
    "default-src 'self'; " .
    "base-uri 'self'; connect-src 'self'; font-src 'self'; form-action 'self'; " .
    "frame-ancestors 'none'; img-src 'self' data:; object-src 'none'; " .
    "script-src 'self' 'nonce-{$nonce}'; style-src 'self' 'nonce-{$nonce}'";

if ($isHttps) {
    $contentSecurityPolicy .= '; upgrade-insecure-requests';
}

header("Content-Security-Policy: {$contentSecurityPolicy}");
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Content-Type-Options: nosniff');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$_SESSION['form_started_at'] = time();

$birthDate = new DateTimeImmutable('1993-09-15');
$today = new DateTimeImmutable('today');
$age = $birthDate->diff($today)->y;
$siteUrl = rtrim(getenv('PORTFOLIO_SITE_URL') ?: 'https://sergiotech.es', '/');

$status = filter_input(INPUT_GET, 'status', FILTER_UNSAFE_RAW);
$formMessage = match ($status) {
    'success' => '¡Gracias! Tu mensaje se ha enviado correctamente.',
    'error' => 'No se ha podido enviar el mensaje. Puedes escribirme directamente por correo.',
    default => '',
};

$structuredData = [
    '@context' => 'https://schema.org',
    '@type' => 'Person',
    'name' => 'Sergio Moreno García',
    'url' => $siteUrl,
    'email' => 'mailto:smorgarc@sergiotech.es',
    'telephone' => '+34614839879',
    'homeLocation' => [
        '@type' => 'Place',
        'name' => 'Murcia, España',
    ],
    'jobTitle' => 'Desarrollador Backend',
    'knowsAbout' => [
        'Desarrollo backend',
        'Desarrollo full-stack',
        'Integración de sistemas',
        'Aplicaciones web',
    ],
];
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sergio Moreno García | Desarrollador Backend</title>
    <meta name="description" content="Portfolio de Sergio Moreno García, desarrollador backend de Murcia con experiencia en banca, telecomunicaciones, integraciones y aplicaciones web.">
    <meta name="author" content="Sergio Moreno García">
    <meta name="robots" content="index, follow, max-image-preview:large">
    <meta name="theme-color" content="#0a0d10">
    <meta name="apple-mobile-web-app-title" content="Sergio Moreno">
    <link rel="canonical" href="<?= htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8') ?>/">
    <link rel="icon" href="assets/icons/favicon-v2.ico" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/icons/favicon-v2-32x32.png">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/icons/apple-touch-icon-v2.png">
    <link rel="manifest" href="site.webmanifest">

    <meta property="og:type" content="profile">
    <meta property="og:locale" content="es_ES">
    <meta property="og:title" content="Sergio Moreno García | Desarrollador Backend">
    <meta property="og:description" content="Desarrollo backend, integración de sistemas y soluciones web construidas para funcionar.">
    <meta property="og:url" content="<?= htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8') ?>/">
    <meta property="profile:first_name" content="Sergio">
    <meta property="profile:last_name" content="Moreno García">
    <meta name="twitter:card" content="summary">

    <?php if ($inlineStyles !== ''): ?>
        <style nonce="<?= htmlspecialchars($nonce, ENT_QUOTES, 'UTF-8') ?>"><?= $inlineStyles ?></style>
    <?php else: ?>
        <link rel="stylesheet" href="assets/css/styles.css?v=2.3.0">
    <?php endif; ?>
    <script type="application/ld+json" nonce="<?= htmlspecialchars($nonce, ENT_QUOTES, 'UTF-8') ?>">
        <?= json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>
    </script>
    <script src="assets/js/main.js?v=2.0.0" defer></script>
</head>
<body>
    <a class="skip-link" href="#contenido">Saltar al contenido</a>

    <header class="site-header" data-header>
        <div class="container header-inner">
            <a class="brand" href="#inicio" aria-label="Ir al inicio">
                <img class="brand-mark" src="assets/icons/sergio-tech-mark-42.webp" srcset="assets/icons/sergio-tech-mark-42.webp 1x, assets/icons/sergio-tech-mark-84.webp 2x" alt="" width="42" height="42" aria-hidden="true" decoding="async">
                <span class="brand-name">Sergio Moreno</span>
            </a>

            <nav class="main-nav" id="main-navigation" aria-label="Navegación principal" data-nav>
                <a href="#sobre-mi">Sobre mí</a>
                <a href="#experiencia">Experiencia</a>
                <a href="#proyectos">Proyectos</a>
                <a href="#contacto">Contacto</a>
            </nav>

            <div class="header-actions">
                <button class="theme-toggle" type="button" aria-label="Cambiar tema de color" title="Cambiar tema" data-theme-toggle>
                    <span class="theme-icon" aria-hidden="true"></span>
                </button>
                <button class="menu-toggle" type="button" aria-controls="main-navigation" aria-expanded="false" aria-label="Abrir menú" data-menu-toggle>
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
                        Desarrollador backend · Murcia
                    </p>
                    <h1 id="hero-title">Construyo el backend que <span>mantiene todo en marcha.</span></h1>
                    <p class="hero-lead">Soy Sergio Moreno García. Transformo necesidades complejas en sistemas fiables, integraciones sólidas y productos digitales preparados para crecer.</p>
                    <div class="hero-actions">
                        <a class="button button-primary" href="#proyectos">Ver proyectos <span aria-hidden="true">↘</span></a>
                        <a class="button button-secondary" href="#contacto">Hablemos</a>
                    </div>
                    <div class="hero-focus" aria-label="Áreas principales">
                        <span>Backend</span>
                        <span>Integraciones</span>
                        <span>Rendimiento</span>
                    </div>
                </div>

                <aside class="hero-portrait" aria-label="Retrato profesional de Sergio Moreno García">
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
                                alt="Sergio Moreno García, desarrollador backend"
                                width="800"
                                height="1000"
                                loading="eager"
                                decoding="async"
                                fetchpriority="high">
                        </picture>
                        <div class="portrait-caption">
                            <div>
                                <strong>Sergio Moreno García</strong>
                                <span>Desarrollador Backend</span>
                            </div>
                            <span class="portrait-location">Murcia · ES</span>
                        </div>
                    </div>
                    <div class="portrait-chip portrait-chip-top" aria-hidden="true">
                        <span>●</span> Backend · Full-stack
                    </div>
                    <div class="portrait-chip portrait-chip-bottom" aria-hidden="true">
                        <small>experiencia</small>
                        <strong>9+ años</strong>
                    </div>
                </aside>
            </div>
            <div class="container hero-metrics" aria-label="Datos destacados">
                <div>
                    <strong>2014</strong>
                    <span>Inicio profesional</span>
                </div>
                <div>
                    <strong>9+ años</strong>
                    <span>En desarrollo</span>
                </div>
                <div>
                    <strong>2 sectores</strong>
                    <span>Banca y telecom</span>
                </div>
            </div>
        </section>

        <div class="expertise-strip" aria-label="Especialidades profesionales">
            <div class="expertise-track">
                <span>Backend</span><i aria-hidden="true">✦</i>
                <span>Arquitectura</span><i aria-hidden="true">✦</i>
                <span>Integraciones</span><i aria-hidden="true">✦</i>
                <span>Producto digital</span><i aria-hidden="true">✦</i>
                <span>Rendimiento</span><i aria-hidden="true">✦</i>
                <span>Fiabilidad</span><i aria-hidden="true">✦</i>
            </div>
        </div>

        <section class="section about" id="sobre-mi" aria-labelledby="about-title">
            <div class="container section-grid">
                <div class="section-heading" data-reveal>
                    <p class="section-number">01 / Sobre mí</p>
                    <h2 id="about-title">Experiencia técnica con visión de producto.</h2>
                </div>
                <div class="about-content" data-reveal>
                    <p class="large-copy">Tengo <strong data-age data-birthdate="1993-09-15"><?= $age ?></strong> años y una trayectoria que une trabajo de campo, desarrollo full-stack y especialización backend.</p>
                    <p>Mi recorrido me ha enseñado a entender tanto la tecnología como el contexto en el que se utiliza. Me centro en construir sistemas mantenibles, resolver problemas con criterio y entregar experiencias digitales rápidas y claras.</p>

                    <div class="capabilities" aria-label="Áreas de especialización">
                        <article>
                            <span>01</span>
                            <h3>Backend</h3>
                            <p>Lógica de negocio, servicios e integraciones preparadas para entornos exigentes.</p>
                        </article>
                        <article>
                            <span>02</span>
                            <h3>Full-stack</h3>
                            <p>Visión completa del producto, desde la experiencia web hasta la capa de servidor.</p>
                        </article>
                        <article>
                            <span>03</span>
                            <h3>Entornos críticos</h3>
                            <p>Experiencia profesional en sistemas de banca y telecomunicaciones.</p>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <section class="section experience" id="experiencia" aria-labelledby="experience-title">
            <div class="container">
                <div class="section-heading split-heading" data-reveal>
                    <div>
                        <p class="section-number">02 / Experiencia</p>
                        <h2 id="experience-title">Una trayectoria construida paso a paso.</h2>
                    </div>
                    <p>Más de una década de experiencia profesional, evolucionando desde las infraestructuras de telecomunicaciones hasta el desarrollo de software para grandes organizaciones.</p>
                </div>

                <ol class="timeline">
                    <li class="timeline-item" data-reveal>
                        <div class="timeline-period">
                            <time datetime="2023-09-04">04.09.2023</time>
                            <span>—</span>
                            <time datetime="2026-01-12">12.01.2026</time>
                        </div>
                        <div class="timeline-role">
                            <h3>Desarrollador Backend</h3>
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
                            <h3>Desarrollador Backend</h3>
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
                            <h3>Desarrollador Backend</h3>
                            <p>Telefónica · Operadoras y CRM</p>
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
                            <h3>Desarrollador Full-Stack</h3>
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
                            <h3>Instalador de Fibra Óptica</h3>
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
                        <p class="section-number">03 / Proyectos</p>
                        <h2 id="projects-title">Productos propios con propósito.</h2>
                    </div>
                    <p>Proyectos digitales en los que aplico experiencia técnica, atención al detalle y una orientación práctica al usuario.</p>
                </div>

                <div class="project-list">
                    <a class="project-showcase project-mwa" href="https://myworkingarea.com/" target="_blank" rel="noopener noreferrer" aria-label="Visitar My Working Area (se abre en una pestaña nueva)" data-reveal>
                        <div class="project-copy">
                            <div class="project-heading-row">
                                <span class="project-index">01</span>
                                <span class="project-status"><i aria-hidden="true"></i> Disponible</span>
                            </div>
                            <p class="project-type">Suite de productividad online</p>
                            <h3>My Working Area</h3>
                            <p class="project-description">Herramientas online para trabajar con PDF, imágenes, documentos y tareas de productividad de forma rápida, privada y sin instalaciones.</p>
                            <ul class="project-tags" aria-label="Características">
                                <li>Producto digital</li>
                                <li>Herramientas web</li>
                                <li>En producción</li>
                            </ul>
                            <span class="project-link">Explorar proyecto <i aria-hidden="true">↗</i></span>
                        </div>
                        <div class="project-browser" aria-hidden="true">
                            <div class="browser-bar">
                                <span></span><span></span><span></span>
                                <p>myworkingarea.com</p>
                            </div>
                            <div class="project-screenshot">
                                <img src="assets/images/myworkingarea-website.webp" alt="Vista de la página principal de My Working Area" width="1280" height="800" loading="lazy" decoding="async">
                            </div>
                        </div>
                    </a>

                    <a class="project-showcase project-vyrsea" href="https://vyrsea.com/" target="_blank" rel="noopener noreferrer" aria-label="Visitar Vyrsea (se abre en una pestaña nueva)" data-reveal>
                        <div class="project-copy">
                            <div class="project-heading-row">
                                <span class="project-index">02</span>
                                <span class="project-status project-status-building"><i aria-hidden="true"></i> En desarrollo y pruebas</span>
                            </div>
                            <p class="project-type">CMS y sistema de reservas</p>
                            <h3>Vyrsea</h3>
                            <p class="project-description">Una solución para que negocios desplieguen su propia web y sistema de reservas autogestionado sobre infraestructura dedicada.</p>
                            <ul class="project-tags" aria-label="Características">
                                <li>CMS</li>
                                <li>Reservas</li>
                                <li>Próximamente</li>
                            </ul>
                            <span class="project-link">Conocer Vyrsea <i aria-hidden="true">↗</i></span>
                        </div>
                        <div class="project-browser" aria-hidden="true">
                            <div class="browser-bar">
                                <span></span><span></span><span></span>
                                <p>vyrsea.com</p>
                            </div>
                            <div class="project-screenshot">
                                <img src="assets/images/vyrsea-website.webp" alt="Vista de la página principal de Vyrsea CMS" width="1280" height="800" loading="lazy" decoding="async">
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </section>

        <section class="section contact" id="contacto" aria-labelledby="contact-title">
            <div class="container contact-grid">
                <div class="contact-intro" data-reveal>
                    <p class="section-number">04 / Contacto</p>
                    <h2 id="contact-title">¿Tienes un proyecto en mente?</h2>
                    <p>Cuéntame qué necesitas. Responderé lo antes posible para valorar cómo puedo ayudarte.</p>

                    <div class="direct-contact">
                        <!--email_off-->
                        <a href="mailto:smorgarc@sergiotech.es">
                            <span>Correo</span>
                            <strong>smorgarc@sergiotech.es</strong>
                        </a>
                        <!--/email_off-->
                        <a href="tel:+34614839879">
                            <span>Teléfono</span>
                            <strong>+34 614 839 879</strong>
                        </a>
                        <div>
                            <span>Residencia</span>
                            <strong>Murcia, España</strong>
                        </div>
                    </div>
                </div>

                <form class="contact-form" action="contact.php" method="post" data-contact-form data-reveal>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                    <div class="honeypot" aria-hidden="true">
                        <label for="website">No rellenar este campo</label>
                        <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="form-row">
                        <div class="field">
                            <label for="name">Nombre <span aria-hidden="true">*</span></label>
                            <input id="name" name="name" type="text" autocomplete="name" maxlength="80" required placeholder="Tu nombre">
                        </div>
                        <div class="field">
                            <label for="email">Correo electrónico <span aria-hidden="true">*</span></label>
                            <input id="email" name="email" type="email" autocomplete="email" inputmode="email" maxlength="120" required placeholder="tu@email.com">
                        </div>
                    </div>

                    <div class="field">
                        <label for="subject">Asunto <span aria-hidden="true">*</span></label>
                        <input id="subject" name="subject" type="text" maxlength="120" required placeholder="¿En qué puedo ayudarte?">
                    </div>

                    <div class="field">
                        <label for="message">Mensaje <span aria-hidden="true">*</span></label>
                        <textarea id="message" name="message" rows="6" minlength="20" maxlength="5000" required placeholder="Cuéntame brevemente tu idea, necesidad o propuesta..."></textarea>
                        <small><span data-character-count>0</span> / 5000</small>
                    </div>

                    <label class="consent">
                        <input name="privacy" type="checkbox" value="accepted" required>
                        <span>He leído y acepto el uso de mis datos exclusivamente para responder a esta consulta.</span>
                    </label>

                    <button class="button button-primary submit-button" type="submit">
                        <span data-submit-label>Enviar mensaje</span>
                        <span aria-hidden="true">↗</span>
                    </button>

                    <p class="form-status<?= $formMessage !== '' ? ' is-visible ' . ($status === 'success' ? 'is-success' : 'is-error') : '' ?>" role="status" aria-live="polite" data-form-status><?= htmlspecialchars($formMessage, ENT_QUOTES, 'UTF-8') ?></p>
                </form>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container footer-inner">
            <a class="brand" href="#inicio" aria-label="Volver al inicio">
                <img class="brand-mark" src="assets/icons/sergio-tech-mark-42.webp" srcset="assets/icons/sergio-tech-mark-42.webp 1x, assets/icons/sergio-tech-mark-84.webp 2x" alt="" width="42" height="42" aria-hidden="true" decoding="async">
                <span class="brand-name">Sergio Moreno García</span>
            </a>
            <p>Diseñado y desarrollado con atención al detalle.</p>
            <p>© <span data-current-year><?= date('Y') ?></span> Sergio Moreno García</p>
        </div>
    </footer>
</body>
</html>
