<?php
declare(strict_types=1);

if (!defined('PORTFOLIO_APP') && PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

/**
 * @return array<string, array<string, string>>
 */
function portfolioTranslations(): array
{
    return [
        'es' => [
            'seo_title' => 'Sergio Moreno García | Desarrollador Backend',
            'seo_description' => 'Portfolio de Sergio Moreno García, desarrollador backend de Murcia con experiencia en banca, telecomunicaciones, integraciones y aplicaciones web.',
            'og_description' => 'Desarrollo backend, integración de sistemas y soluciones web construidas para funcionar.',
            'skip_content' => 'Saltar al contenido',
            'go_home' => 'Ir al inicio',
            'main_navigation' => 'Navegación principal',
            'nav_about' => 'Sobre mí',
            'nav_experience' => 'Experiencia',
            'nav_projects' => 'Proyectos',
            'nav_contact' => 'Contacto',
            'language_selector' => 'Seleccionar idioma',
            'language_spanish' => 'Ver sitio en español',
            'language_english' => 'View site in English',
            'theme_change' => 'Cambiar tema de color',
            'theme_title' => 'Cambiar tema',
            'theme_enable_dark' => 'Activar tema oscuro',
            'theme_enable_light' => 'Activar tema claro',
            'menu_open' => 'Abrir menú',
            'menu_close' => 'Cerrar menú',
            'hero_eyebrow' => 'Desarrollador backend · Murcia',
            'hero_title' => 'Construyo el backend que',
            'hero_title_highlight' => 'mantiene todo en marcha.',
            'hero_lead' => 'Soy Sergio Moreno García. Transformo necesidades complejas en sistemas fiables, integraciones sólidas y productos digitales preparados para crecer.',
            'hero_projects' => 'Ver proyectos',
            'hero_contact' => 'Hablemos',
            'focus_label' => 'Áreas principales',
            'focus_integrations' => 'Integraciones',
            'focus_performance' => 'Rendimiento',
            'portrait_label' => 'Retrato profesional de Sergio Moreno García',
            'portrait_alt' => 'Sergio Moreno García, desarrollador backend',
            'backend_developer' => 'Desarrollador Backend',
            'portrait_experience' => 'experiencia',
            'years_9' => '9+ años',
            'highlights_label' => 'Datos destacados',
            'professional_start' => 'Inicio profesional',
            'in_development' => 'En desarrollo',
            'sectors_2' => '2 sectores',
            'banking_telecom' => 'Banca y telecom',
            'expertise_label' => 'Especialidades profesionales',
            'expertise_architecture' => 'Arquitectura',
            'expertise_integrations' => 'Integraciones',
            'expertise_digital_product' => 'Producto digital',
            'expertise_performance' => 'Rendimiento',
            'expertise_reliability' => 'Fiabilidad',
            'about_number' => '01 / Sobre mí',
            'about_title' => 'Experiencia técnica con visión de producto.',
            'about_age_before' => 'Tengo',
            'about_age_after' => 'años y una trayectoria que une trabajo de campo, desarrollo full-stack y especialización backend.',
            'about_body' => 'Mi recorrido me ha enseñado a entender tanto la tecnología como el contexto en el que se utiliza. Me centro en construir sistemas mantenibles, resolver problemas con criterio y entregar experiencias digitales rápidas y claras.',
            'capabilities_label' => 'Áreas de especialización',
            'capability_backend' => 'Lógica de negocio, servicios e integraciones preparadas para entornos exigentes.',
            'capability_fullstack' => 'Visión completa del producto, desde la experiencia web hasta la capa de servidor.',
            'capability_critical_title' => 'Entornos críticos',
            'capability_critical' => 'Experiencia profesional en sistemas de banca y telecomunicaciones.',
            'experience_number' => '02 / Experiencia',
            'experience_title' => 'Una trayectoria construida paso a paso.',
            'experience_intro' => 'Más de una década de experiencia profesional, evolucionando desde las infraestructuras de telecomunicaciones hasta el desarrollo de software para grandes organizaciones.',
            'role_backend' => 'Desarrollador Backend',
            'role_fullstack' => 'Desarrollador Full-Stack',
            'role_fiber' => 'Instalador de Fibra Óptica',
            'telefonica_unit' => 'Telefónica · Operadoras y CRM',
            'projects_number' => '03 / Proyectos',
            'projects_title' => 'Productos propios con propósito.',
            'projects_intro' => 'Proyectos digitales en los que aplico experiencia técnica, atención al detalle y una orientación práctica al usuario.',
            'mwa_link_label' => 'Visitar My Working Area (se abre en una pestaña nueva)',
            'status_available' => 'Disponible',
            'mwa_type' => 'Suite de productividad online',
            'mwa_description' => 'Herramientas online para trabajar con PDF, imágenes, documentos y tareas de productividad de forma rápida, privada y sin instalaciones.',
            'features_label' => 'Características',
            'tag_digital_product' => 'Producto digital',
            'tag_web_tools' => 'Herramientas web',
            'tag_production' => 'En producción',
            'explore_project' => 'Explorar proyecto',
            'mwa_alt' => 'Vista de la página principal de My Working Area',
            'vyrsea_link_label' => 'Visitar Vyrsea (se abre en una pestaña nueva)',
            'status_building' => 'En desarrollo y pruebas',
            'vyrsea_type' => 'CMS y sistema de reservas',
            'vyrsea_description' => 'Una solución para que negocios desplieguen su propia web y sistema de reservas autogestionado sobre infraestructura dedicada.',
            'tag_bookings' => 'Reservas',
            'tag_soon' => 'Próximamente',
            'discover_vyrsea' => 'Conocer Vyrsea',
            'vyrsea_alt' => 'Vista de la página principal de Vyrsea CMS',
            'contact_number' => '04 / Contacto',
            'contact_title' => '¿Tienes un proyecto en mente?',
            'contact_intro' => 'Cuéntame qué necesitas. Responderé lo antes posible para valorar cómo puedo ayudarte.',
            'contact_email' => 'Correo',
            'contact_phone' => 'Teléfono',
            'contact_location' => 'Residencia',
            'location' => 'Murcia, España',
            'honeypot' => 'No rellenar este campo',
            'form_name' => 'Nombre',
            'form_name_placeholder' => 'Tu nombre',
            'form_email' => 'Correo electrónico',
            'form_subject' => 'Asunto',
            'form_subject_placeholder' => '¿En qué puedo ayudarte?',
            'form_message' => 'Mensaje',
            'form_message_placeholder' => 'Cuéntame brevemente tu idea, necesidad o propuesta...',
            'form_consent' => 'He leído y acepto el uso de mis datos exclusivamente para responder a esta consulta.',
            'form_submit' => 'Enviar mensaje',
            'form_sending' => 'Enviando…',
            'form_generic_error' => 'No se ha podido completar el envío.',
            'form_network_error' => 'No hay conexión con el servidor. Puedes escribirme directamente a smorgarc@sergiotech.es.',
            'form_success' => '¡Gracias! Tu mensaje se ha enviado correctamente.',
            'form_redirect_error' => 'No se ha podido enviar el mensaje. Puedes escribirme directamente por correo.',
            'footer_home' => 'Volver al inicio',
            'footer_note' => 'Diseñado y desarrollado con atención al detalle.',
            'contact_method_not_allowed' => 'Método no permitido.',
            'contact_too_large' => 'La solicitud es demasiado grande.',
            'contact_invalid_request' => 'La solicitud no es válida.',
            'contact_expired' => 'La sesión ha caducado. Recarga la página e inténtalo de nuevo.',
            'contact_too_fast' => 'Espera un instante antes de enviar el formulario.',
            'contact_rate_limit' => 'Espera un minuto antes de enviar otro mensaje.',
            'contact_invalid_fields' => 'Revisa los campos obligatorios e inténtalo de nuevo.',
            'contact_unavailable' => 'El formulario no está disponible temporalmente. Escríbeme directamente por correo.',
            'contact_send_error' => 'No se ha podido enviar el mensaje. Escríbeme directamente a smorgarc@sergiotech.es.',
            'email_heading' => 'Nuevo mensaje desde el portfolio',
            'email_name' => 'Nombre',
            'email_address' => 'Correo',
            'email_subject' => 'Asunto',
            'email_message' => 'Mensaje',
            'email_consent' => 'Consentimiento de privacidad: aceptado',
            'email_date' => 'Fecha (UTC)',
            'schema_job_title' => 'Desarrollador Backend',
            'schema_backend' => 'Desarrollo backend',
            'schema_fullstack' => 'Desarrollo full-stack',
            'schema_integrations' => 'Integración de sistemas',
            'schema_web_apps' => 'Aplicaciones web',
        ],
        'en' => [
            'seo_title' => 'Sergio Moreno García | Backend Developer',
            'seo_description' => 'Portfolio of Sergio Moreno García, a backend developer based in Murcia with experience in banking, telecommunications, integrations and web applications.',
            'og_description' => 'Reliable backend systems, seamless integrations and web solutions engineered to perform.',
            'skip_content' => 'Skip to content',
            'go_home' => 'Go to the top',
            'main_navigation' => 'Main navigation',
            'nav_about' => 'About',
            'nav_experience' => 'Experience',
            'nav_projects' => 'Projects',
            'nav_contact' => 'Contact',
            'language_selector' => 'Select language',
            'language_spanish' => 'Ver sitio en español',
            'language_english' => 'View site in English',
            'theme_change' => 'Change color theme',
            'theme_title' => 'Change theme',
            'theme_enable_dark' => 'Enable dark theme',
            'theme_enable_light' => 'Enable light theme',
            'menu_open' => 'Open menu',
            'menu_close' => 'Close menu',
            'hero_eyebrow' => 'Backend developer · Murcia',
            'hero_title' => 'I build the backend that',
            'hero_title_highlight' => 'keeps everything running.',
            'hero_lead' => 'I’m Sergio Moreno García. I turn complex requirements into reliable systems, seamless integrations and scalable digital products.',
            'hero_projects' => 'View projects',
            'hero_contact' => 'Let’s talk',
            'focus_label' => 'Core areas',
            'focus_integrations' => 'Integrations',
            'focus_performance' => 'Performance',
            'portrait_label' => 'Professional portrait of Sergio Moreno García',
            'portrait_alt' => 'Sergio Moreno García, backend developer',
            'backend_developer' => 'Backend Developer',
            'portrait_experience' => 'experience',
            'years_9' => '9+ years',
            'highlights_label' => 'Career highlights',
            'professional_start' => 'Career started',
            'in_development' => 'Building software',
            'sectors_2' => '2 industries',
            'banking_telecom' => 'Banking and telecom',
            'expertise_label' => 'Professional expertise',
            'expertise_architecture' => 'Architecture',
            'expertise_integrations' => 'Integrations',
            'expertise_digital_product' => 'Digital products',
            'expertise_performance' => 'Performance',
            'expertise_reliability' => 'Reliability',
            'about_number' => '01 / About',
            'about_title' => 'Technical expertise with a product mindset.',
            'about_age_before' => 'I’m',
            'about_age_after' => 'years old, and my career spans hands-on fieldwork, full-stack development and backend specialization.',
            'about_body' => 'My experience has taught me to understand not just the technology, but the context in which people use it. I focus on building maintainable systems, making sound technical decisions and delivering fast, intuitive digital experiences.',
            'capabilities_label' => 'Areas of expertise',
            'capability_backend' => 'Business logic, services and integrations engineered for demanding environments.',
            'capability_fullstack' => 'A complete view of the product, from the web experience to the server-side architecture.',
            'capability_critical_title' => 'Critical environments',
            'capability_critical' => 'Hands-on experience with mission-critical banking and telecommunications systems.',
            'experience_number' => '02 / Experience',
            'experience_title' => 'A career built one step at a time.',
            'experience_intro' => 'Over a decade of professional experience, from telecommunications infrastructure to software engineering for large organizations.',
            'role_backend' => 'Backend Developer',
            'role_fullstack' => 'Full-Stack Developer',
            'role_fiber' => 'Fiber Optic Installer',
            'telefonica_unit' => 'Telefónica · Telecom Operators & CRM',
            'projects_number' => '03 / Projects',
            'projects_title' => 'Independent products built with purpose.',
            'projects_intro' => 'Digital products where I combine technical expertise, attention to detail and a practical, user-first approach.',
            'mwa_link_label' => 'Visit My Working Area (opens in a new tab)',
            'status_available' => 'Live',
            'mwa_type' => 'Online productivity suite',
            'mwa_description' => 'A collection of online tools for working with PDFs, images, documents and everyday productivity tasks—quickly, privately and with nothing to install.',
            'features_label' => 'Features',
            'tag_digital_product' => 'Digital product',
            'tag_web_tools' => 'Web tools',
            'tag_production' => 'In production',
            'explore_project' => 'Explore project',
            'mwa_alt' => 'My Working Area homepage',
            'vyrsea_link_label' => 'Visit Vyrsea (opens in a new tab)',
            'status_building' => 'In development and testing',
            'vyrsea_type' => 'CMS and booking system',
            'vyrsea_description' => 'A platform that gives businesses their own website and self-managed booking system, deployed on dedicated infrastructure.',
            'tag_bookings' => 'Bookings',
            'tag_soon' => 'Coming soon',
            'discover_vyrsea' => 'Discover Vyrsea',
            'vyrsea_alt' => 'Vyrsea CMS homepage',
            'contact_number' => '04 / Contact',
            'contact_title' => 'Have a project in mind?',
            'contact_intro' => 'Tell me what you need. I’ll get back to you as soon as possible to discuss how I can help.',
            'contact_email' => 'Email',
            'contact_phone' => 'Phone',
            'contact_location' => 'Based in',
            'location' => 'Murcia, Spain',
            'honeypot' => 'Leave this field empty',
            'form_name' => 'Name',
            'form_name_placeholder' => 'Your name',
            'form_email' => 'Email address',
            'form_subject' => 'Subject',
            'form_subject_placeholder' => 'How can I help?',
            'form_message' => 'Message',
            'form_message_placeholder' => 'Briefly tell me about your idea, project or requirements...',
            'form_consent' => 'I agree that my data may be used solely to respond to this inquiry.',
            'form_submit' => 'Send message',
            'form_sending' => 'Sending…',
            'form_generic_error' => 'The message could not be sent.',
            'form_network_error' => 'The server could not be reached. You can email me directly at smorgarc@sergiotech.es.',
            'form_success' => 'Thank you! Your message has been sent successfully.',
            'form_redirect_error' => 'The message could not be sent. You can email me directly instead.',
            'footer_home' => 'Back to the top',
            'footer_note' => 'Designed and developed with attention to detail.',
            'contact_method_not_allowed' => 'Method not allowed.',
            'contact_too_large' => 'The request is too large.',
            'contact_invalid_request' => 'The request is not valid.',
            'contact_expired' => 'Your session has expired. Reload the page and try again.',
            'contact_too_fast' => 'Please wait a moment before submitting the form.',
            'contact_rate_limit' => 'Please wait one minute before sending another message.',
            'contact_invalid_fields' => 'Check the required fields and try again.',
            'contact_unavailable' => 'The form is temporarily unavailable. Please email me directly.',
            'contact_send_error' => 'The message could not be sent. Please email me directly at smorgarc@sergiotech.es.',
            'email_heading' => 'New message from the portfolio',
            'email_name' => 'Name',
            'email_address' => 'Email',
            'email_subject' => 'Subject',
            'email_message' => 'Message',
            'email_consent' => 'Privacy consent: accepted',
            'email_date' => 'Date (UTC)',
            'schema_job_title' => 'Backend Developer',
            'schema_backend' => 'Backend development',
            'schema_fullstack' => 'Full-stack development',
            'schema_integrations' => 'Systems integration',
            'schema_web_apps' => 'Web applications',
        ],
    ];
}

function portfolioSupportedLanguage(mixed $value): ?string
{
    if (!is_string($value)) {
        return null;
    }

    $language = strtolower(trim($value));
    return in_array($language, ['es', 'en'], true) ? $language : null;
}

function portfolioBrowserLanguage(?string $acceptLanguage): ?string
{
    if ($acceptLanguage === null || trim($acceptLanguage) === '') {
        return null;
    }

    $preferences = [];
    foreach (explode(',', $acceptLanguage) as $position => $entry) {
        $parts = array_map('trim', explode(';', $entry));
        $tag = strtolower($parts[0] ?? '');

        if ($tag === '' || $tag === '*' || preg_match('/^[a-z]{2,8}(?:-[a-z0-9]{1,8})*$/i', $tag) !== 1) {
            continue;
        }

        $quality = 1.0;
        foreach (array_slice($parts, 1) as $parameter) {
            if (preg_match('/^q=(0(?:\.\d{1,3})?|1(?:\.0{1,3})?)$/i', $parameter, $matches) === 1) {
                $quality = (float) $matches[1];
            }
        }

        if ($quality > 0) {
            $preferences[] = ['tag' => $tag, 'quality' => $quality, 'position' => $position];
        }
    }

    if ($preferences === []) {
        return null;
    }

    usort(
        $preferences,
        static fn(array $left, array $right): int =>
            $right['quality'] <=> $left['quality'] ?: $left['position'] <=> $right['position']
    );

    return str_starts_with($preferences[0]['tag'], 'es') ? 'es' : 'en';
}

function portfolioVisitorCountryCode(): ?string
{
    foreach (['HTTP_CF_IPCOUNTRY', 'HTTP_X_COUNTRY_CODE', 'GEOIP_COUNTRY_CODE'] as $header) {
        $countryCode = strtoupper(trim((string) ($_SERVER[$header] ?? '')));
        if (preg_match('/^[A-Z]{2}$/', $countryCode) === 1 && !in_array($countryCode, ['XX', 'T1'], true)) {
            return $countryCode;
        }
    }

    return null;
}

function portfolioCountryUsesSpanish(?string $countryCode): bool
{
    return $countryCode !== null && in_array(
        $countryCode,
        ['AR', 'BO', 'CL', 'CO', 'CR', 'CU', 'DO', 'EC', 'ES', 'GQ', 'GT', 'HN', 'MX', 'NI', 'PA', 'PE', 'PR', 'PY', 'SV', 'UY', 'VE'],
        true
    );
}

function portfolioDetectLanguage(): string
{
    $savedLanguage = portfolioSupportedLanguage($_COOKIE['portfolio_lang'] ?? null);
    if ($savedLanguage !== null) {
        return $savedLanguage;
    }

    $browserLanguage = portfolioBrowserLanguage($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null);
    if ($browserLanguage !== null) {
        return $browserLanguage;
    }

    return portfolioCountryUsesSpanish(portfolioVisitorCountryCode()) ? 'es' : 'en';
}

function portfolioSetLanguageCookie(string $language, bool $secure): void
{
    setcookie('portfolio_lang', $language, [
        'expires' => time() + 31_536_000,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function portfolioText(string $language, string $key): string
{
    $translations = portfolioTranslations();
    return $translations[$language][$key] ?? $translations['en'][$key] ?? $key;
}
