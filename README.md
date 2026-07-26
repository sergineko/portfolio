# Portfolio de Sergio Moreno García

Portfolio profesional de [Sergio Moreno García](https://sergiotech.es), desarrollado exclusivamente con HTML5, CSS3, JavaScript nativo y PHP.

El sitio presenta el perfil profesional, la experiencia laboral y los proyectos personales de Sergio mediante un diseño responsive, bilingüe y optimizado para buscadores y rendimiento. Incluye un formulario de contacto con entrega real de correo y estadísticas opcionales mediante Umami.

- Sitio web: [https://sergiotech.es](https://sergiotech.es)
- Repositorio: [sergineko/portfolio](https://github.com/sergineko/portfolio)
- Rama de producción: `main`

## Estado actual y últimas actualizaciones

- Diseño moderno con temas claro y oscuro.
- Retrato profesional personalizado y adaptado a pantallas grandes y móviles.
- Capturas reales y enlaces funcionales para My Working Area y Vyrsea.
- Identidad visual propia, favicon e iconos PWA.
- Imágenes responsive en AVIF y WebP para mejorar el LCP.
- Sitio completamente disponible en español e inglés estadounidense.
- Selección automática de idioma mediante navegador y país, con selector manual persistente.
- SEO, Open Graph, datos estructurados y mensajes accesibles traducidos.
- Formulario de contacto localizado con validación, medidas antispam y envío real.
- Entrega de correo sin credenciales SMTP obligatorias mediante `mail()` o conexión directa al MX.
- Estadísticas opcionales con Umami Cloud o una instancia propia.
- Eventos de conversión para proyectos, contacto, idioma y envíos correctos.
- Pruebas automatizadas para idiomas y transporte SMTP.
- Documentación de despliegue mediante Coolify y Nixpacks.

## Funcionalidades

### Presentación profesional

- Hero con especialización, propuesta de valor y llamadas a la acción.
- Edad calculada dinámicamente a partir del `15/09/1993`.
- Trayectoria profesional completa desde 2014.
- Métricas destacadas y áreas de especialización.
- Diseño accesible, responsive y compatible con movimiento reducido.
- Tema claro u oscuro según la preferencia del sistema, con selección persistente.

### Proyectos

- [My Working Area](https://myworkingarea.com/), disponible en producción.
- [Vyrsea](https://vyrsea.com/), en desarrollo y pruebas.
- Capturas reales, estados, descripciones y enlaces externos seguros.
- Carga diferida de las capturas para evitar penalizar la carga inicial.

### Idiomas

- Contenido completo en español e inglés estadounidense.
- Traducción de textos visibles, metadatos SEO, accesibilidad, datos estructurados y respuestas del formulario.
- Selector manual `ES / EN`.
- Preferencia guardada durante un año.
- Detección alternativa por país cuando el navegador no informa de un idioma.
- Inglés como idioma seguro cuando no se puede identificar idioma ni país.

### Formulario de contacto

- Envío asíncrono mediante `fetch`.
- Redirección HTTP como alternativa si JavaScript no está disponible.
- Respuestas y correos localizados en español o inglés.
- Renovación del token CSRF después de cada envío correcto.
- `Reply-To` configurado con el correo del visitante.
- Transporte local mediante `mail()` y entrega SMTP directa al servidor MX como alternativa.

### Estadísticas

- Integración opcional con Umami Cloud o una instancia propia.
- Carga condicional: no se realiza ninguna petición si las variables no están configuradas correctamente.
- Restricción por dominio y origen HTTPS.
- Compatibilidad con **Do Not Track**.
- Exclusión de parámetros de búsqueda.
- Etiquetado de visitas por idioma.
- Eventos sin datos personales ni contenido del formulario.

## Tecnologías y requisitos

### Aplicación

- PHP 8.1 o superior.
- HTML5 semántico.
- CSS3 responsive, sin frameworks.
- JavaScript nativo, sin dependencias.
- Apache o Nginx con soporte PHP.

### Requisitos opcionales para el correo

- Función `mail()` configurada, o bien:
- Resolución DNS mediante `getmxrr()` o `dns_get_record()`.
- Extensión OpenSSL para negociar `STARTTLS`.
- Conexiones TCP salientes al puerto `25`.

No se necesita Node.js, un gestor de paquetes, una base de datos ni un proceso de compilación para ejecutar el portfolio.

## Estructura del proyecto

```text
.
├── assets/
│   ├── css/
│   │   └── styles.css
│   ├── icons/
│   │   ├── favicon e iconos PWA
│   │   └── marca Sergio Tech en WebP
│   ├── images/
│   │   ├── retrato responsive en AVIF y WebP
│   │   └── capturas de los proyectos
│   └── js/
│       └── main.js
├── tests/
│   ├── localization_test.php
│   └── mailer_test.php
├── .env.example
├── .gitignore
├── .htaccess
├── contact.php
├── index.php
├── localization.php
├── mailer.php
├── robots.txt
├── site.webmanifest
└── sitemap.xml
```

### Responsabilidad de los archivos PHP

| Archivo | Responsabilidad |
|---|---|
| `index.php` | Renderizado principal, idioma, SEO, CSP, Umami, sesión y token CSRF. |
| `localization.php` | Catálogos ES/EN, detección del navegador, país y preferencia guardada. |
| `contact.php` | Validación del formulario, antispam, respuestas JSON y composición del correo. |
| `mailer.php` | Transporte mediante `mail()`, resolución MX, SMTP directo y `STARTTLS`. |

## Ejecución local

Con PHP instalado:

```bash
php -S 127.0.0.1:8080
```

Después abre:

- Español: [http://127.0.0.1:8080/?lang=es](http://127.0.0.1:8080/?lang=es)
- Inglés: [http://127.0.0.1:8080/?lang=en](http://127.0.0.1:8080/?lang=en)

El servidor integrado de PHP es adecuado para desarrollo, pero no debe utilizarse como servidor de producción.

### Variables para desarrollo local

El proyecto incluye [`.env.example`](.env.example) como referencia, pero no carga archivos `.env` automáticamente. Las variables deben definirse en el proceso que ejecuta PHP.

PowerShell:

```powershell
$env:PORTFOLIO_SITE_URL = "http://127.0.0.1:8080"
$env:PORTFOLIO_TO_EMAIL = "smorgarc@sergiotech.es"
$env:PORTFOLIO_FROM_EMAIL = "no-reply@sergiotech.es"
php -S 127.0.0.1:8080
```

Bash:

```bash
export PORTFOLIO_SITE_URL="http://127.0.0.1:8080"
export PORTFOLIO_TO_EMAIL="smorgarc@sergiotech.es"
export PORTFOLIO_FROM_EMAIL="no-reply@sergiotech.es"
php -S 127.0.0.1:8080
```

No configures Umami durante el desarrollo si no quieres registrar visitas locales. `UMAMI_DOMAINS=sergiotech.es` también evita que el tracker se ejecute sobre `127.0.0.1`.

## Variables de entorno

Configuración de referencia:

```dotenv
PORTFOLIO_SITE_URL=https://sergiotech.es
PORTFOLIO_TO_EMAIL=smorgarc@sergiotech.es
PORTFOLIO_FROM_EMAIL=no-reply@sergiotech.es

UMAMI_SCRIPT_URL=https://cloud.umami.is/script.js
UMAMI_WEBSITE_ID=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
UMAMI_DOMAINS=sergiotech.es
```

| Variable | Obligatoria | Finalidad |
|---|---:|---|
| `PORTFOLIO_SITE_URL` | Recomendada | URL canónica usada por SEO, Open Graph y datos estructurados. |
| `PORTFOLIO_TO_EMAIL` | Recomendada | Buzón que recibe los mensajes del formulario. |
| `PORTFOLIO_FROM_EMAIL` | Recomendada | Remitente del servidor; debe pertenecer a un dominio autorizado. |
| `UMAMI_SCRIPT_URL` | No | URL HTTPS de `script.js` en Umami Cloud o en una instancia propia. |
| `UMAMI_WEBSITE_ID` | No | UUID asignado al sitio dentro de Umami. |
| `UMAMI_DOMAINS` | No | Lista de dominios autorizados, separados por comas. |

La aplicación utiliza valores adecuados para `sergiotech.es` cuando no se definen las variables `PORTFOLIO_*`. En producción se recomienda declararlas explícitamente.

Umami permanece desactivado salvo que la URL sea HTTPS, el `Website ID` tenga formato UUID y ambas variables estén presentes.

## Selección de idioma

La prioridad aplicada en cada petición es:

1. Parámetro explícito `?lang=es` o `?lang=en`.
2. Cookie `portfolio_lang` creada por el selector manual.
3. Idioma preferido del navegador mediante `Accept-Language`.
4. País enviado por el proxy mediante:
   - `CF-IPCountry`
   - `X-Country-Code`
   - `GEOIP_COUNTRY_CODE`
5. Inglés cuando no hay información válida.

Un idioma principal distinto del español muestra la versión inglesa. El país solo se consulta cuando `Accept-Language` no aporta una preferencia válida.

La selección manual se guarda durante un año en una cookie `HttpOnly`, `SameSite=Lax` y `Secure` bajo HTTPS.

La respuesta incluye `Content-Language` y una cabecera `Vary` con idioma, cookie y cabeceras de país. Si se configura una regla **Cache Everything** en Cloudflare, su clave de caché debe respetar estas variantes; de lo contrario, distintos visitantes podrían recibir el mismo idioma cacheado.

## Estadísticas con Umami

### Activación

1. Crea el sitio `sergiotech.es` en Umami.
2. Copia el `Website ID` mostrado en su código de seguimiento.
3. Configura las variables `UMAMI_*` en Coolify.
4. Despliega nuevamente la aplicación.
5. Visita el portfolio sin un bloqueador de rastreadores.
6. Comprueba la visita en el panel de Umami.

Para Umami Cloud:

```dotenv
UMAMI_SCRIPT_URL=https://cloud.umami.is/script.js
UMAMI_WEBSITE_ID=TU-UUID
UMAMI_DOMAINS=sergiotech.es
```

Para una instancia propia:

```dotenv
UMAMI_SCRIPT_URL=https://analytics.example.com/script.js
UMAMI_WEBSITE_ID=TU-UUID
UMAMI_DOMAINS=sergiotech.es
```

La política CSP se amplía dinámicamente solo con el origen exacto de `UMAMI_SCRIPT_URL`, tanto para cargar el script como para enviar los eventos.

### Datos y eventos registrados

Umami registra automáticamente las páginas vistas. El atributo `data-tag` identifica el idioma servido como `lang-es` o `lang-en`.

También se registran:

| Evento | Información asociada |
|---|---|
| `language-switch` | Idioma elegido. |
| `projects-cta` | Acceso desde el CTA principal. |
| `contact-cta` | Acceso desde el CTA de contacto. |
| `project-open` | Proyecto abierto: `myworkingarea` o `vyrsea`. |
| `contact-link` | Método seleccionado: correo o teléfono. |
| `contact-form-success` | Idioma del formulario enviado correctamente. |

No se envían nombres, direcciones de correo, teléfonos ni el contenido del mensaje a Umami.

La configuración activa:

- `data-domains` para limitar los hosts admitidos.
- `data-do-not-track="true"` para respetar la preferencia del navegador.
- `data-exclude-search="true"` para no registrar parámetros de consulta.
- `data-tag` para diferenciar el idioma.

Si las variables faltan o no son válidas, el script no se añade al HTML y la CSP permanece cerrada a orígenes externos.

## Formulario y envío de correo

### Flujo del formulario

1. `index.php` crea la sesión, el token CSRF y la marca de tiempo inicial.
2. JavaScript valida el formulario y envía los datos a `contact.php`.
3. El endpoint valida seguridad, longitudes, consentimiento y frecuencia.
4. `mailer.php` intenta entregar el mensaje.
5. La respuesta JSON actualiza el estado del formulario y renueva el token CSRF.
6. Sin JavaScript, el endpoint devuelve una redirección `303` con estado de éxito o error.

### Cabeceras del correo

- `From`: `PORTFOLIO_FROM_EMAIL`.
- `To`: `PORTFOLIO_TO_EMAIL`.
- `Reply-To`: correo introducido por el visitante.
- Asunto: `Portfolio: {asunto indicado}`.
- Cuerpo: nombre, correo, asunto, mensaje, consentimiento y fecha UTC.

El correo del visitante no se utiliza como remitente, lo que reduce rechazos relacionados con SPF y DMARC.

### Orden de transporte

1. Utiliza `mail()` cuando existe un transporte local funcional.
2. Si falla, consulta los registros MX del dominio destinatario.
3. Intenta hasta tres servidores MX por orden de prioridad.
4. Negocia `STARTTLS` cuando el servidor lo anuncia y OpenSSL está disponible.
5. Entrega el mensaje directamente al MX por SMTP.

No es necesario configurar `SMTP_HOST`, usuario, contraseña ni una API de terceros.

### Requisitos de la entrega directa

- Resolución DNS disponible dentro del contenedor.
- Conexiones TCP salientes al puerto `25`.
- Puerto saliente no bloqueado por el proveedor del VPS.
- SPF autorizando la IP pública del servidor.
- PTR o DNS inverso coherente.
- DMARC recomendado.

No se debe publicar el puerto `25` en Coolify: la aplicación solo inicia conexiones salientes.

Comprobación MX desde el contenedor:

```bash
php -r '$email=getenv("PORTFOLIO_TO_EMAIL"); $domain=substr(strrchr($email, "@"), 1); var_dump(getmxrr($domain, $hosts, $weights), $hosts, $weights);'
```

> [!IMPORTANT]
> Si el proveedor bloquea el puerto saliente `25`, la entrega directa no puede funcionar utilizando únicamente las direcciones `From` y `To`. Será necesario solicitar el desbloqueo o incorporar un relay SMTP autenticado.

Una respuesta SMTP `250` confirma que el servidor MX ha aceptado el mensaje, pero los filtros posteriores todavía pueden enviarlo a spam.

## Seguridad

### Página principal

- Content Security Policy con nonce por petición.
- Orígenes de Umami permitidos únicamente cuando la configuración es válida.
- `frame-ancestors 'none'`.
- `object-src 'none'`.
- `base-uri` y `form-action` limitados al propio sitio.
- `Referrer-Policy: strict-origin-when-cross-origin`.
- `X-Content-Type-Options: nosniff`.
- Actualización automática de recursos inseguros bajo HTTPS.

### Formulario

- Token CSRF por sesión.
- Cookies `HttpOnly`, `SameSite=Lax` y `Secure` bajo HTTPS.
- Validación del origen de la petición.
- Honeypot invisible para bots.
- Espera mínima de dos segundos antes de enviar.
- Máximo de un mensaje por minuto y sesión.
- Peticiones limitadas a 20 KB.
- Validación de nombre, correo, asunto y mensaje.
- Mensajes entre 20 y 5000 caracteres.
- Protección frente a inyección de cabeceras.
- Consentimiento obligatorio.
- Endpoint sin caché.
- Respuestas JSON sin incluir detalles internos del transporte.

### Servidor Apache

`.htaccess` añade:

- Desactivación del listado de directorios.
- HSTS, políticas del navegador y cabeceras adicionales.
- Compresión de recursos.
- Caché prolongada para recursos estáticos.
- Denegación de acceso a archivos internos, logs, configuración y documentación.

Nginx no procesa `.htaccess`; consulta la sección de Coolify para trasladar las reglas necesarias.

## SEO y rendimiento

### SEO

- HTML5 semántico.
- Títulos y descripciones localizados.
- Open Graph adaptado al idioma.
- Datos estructurados `Person` mediante JSON-LD.
- URL canónica específica para cada idioma.
- Enlaces `hreflang` para `es`, `en` y `x-default`.
- `sitemap.xml` con las dos variantes.
- `robots.txt`.
- Edad actual calculada dinámicamente.
- Enlaces externos con `noopener noreferrer`.

### Rendimiento

- CSS crítico integrado en la respuesta para evitar una solicitud bloqueante.
- JavaScript diferido.
- Tracker de Umami diferido y completamente opcional.
- Sin fuentes externas ni frameworks.
- Retrato responsive en AVIF y WebP con variantes de 480, 640 y 800 píxeles.
- `srcset` y `sizes` para descargar la imagen adecuada.
- `fetchpriority="high"` y carga inmediata para la imagen LCP.
- Capturas de proyectos con `loading="lazy"`.
- Iconos de marca optimizados en WebP.
- Dimensiones declaradas para reducir cambios de diseño.
- Favicon, Apple Touch Icon y manifest PWA.
- Compatibilidad con `prefers-reduced-motion`.
- Caché prolongada para recursos estáticos bajo Apache.

## Despliegue con Coolify

### 1. Crear la aplicación

1. Entra en el proyecto y entorno de Coolify.
2. Selecciona **New Resource**.
3. Para un repositorio público, utiliza:

   ```text
   https://github.com/sergineko/portfolio
   ```

4. Para un repositorio privado, utiliza una GitHub App o una Deploy Key.
5. Selecciona la rama `main`.

### 2. Configurar Nixpacks

| Opción | Valor |
|---|---|
| Build Pack | `Nixpacks` |
| Base Directory | `/` |
| PHP Root Directory | raíz del repositorio |
| Port Exposes | `80` |
| Health Check Path | `/` |
| Build Command | vacío |
| Start Command | vacío |

Nixpacks detecta PHP mediante `index.php`, configura el servidor web y sirve la raíz del repositorio.

> [!IMPORTANT]
> El proveedor PHP de Nixpacks utiliza Nginx, por lo que `.htaccess` no se aplica. Las cabeceras generadas desde PHP, incluida la CSP, siguen funcionando; las reglas adicionales de Apache para HSTS, compresión y caché deben trasladarse a Nginx o Cloudflare si se necesitan.

### 3. Configurar dominio y HTTPS

1. Añade `https://sergiotech.es` en **Domains**.
2. Apunta el DNS del dominio a la IP del servidor.
3. Activa HTTPS.
4. Fuerza la redirección de HTTP a HTTPS.
5. No publiques un puerto del host; Coolify dirige el dominio al puerto interno.

### 4. Configurar variables

1. Abre **Environment Variables → Developer View**.
2. Copia las variables de la sección correspondiente.
3. Sustituye el UUID de Umami por el valor real.
4. Configúralas como variables de ejecución o **Runtime Variables**.
5. No guardes contraseñas ni secretos en Git.

### 5. Desplegar

1. Guarda la configuración.
2. Pulsa **Deploy**.
3. Verifica en los logs que la aplicación escucha en el puerto `80`.
4. Comprueba `/`, `/?lang=es`, `/?lang=en`, `/contact.php` y los recursos de `assets/`.
5. Envía un mensaje real desde el formulario.
6. Comprueba una visita y los eventos en Umami.
7. Activa **Auto Deploy** si quieres publicar automáticamente cada push a `main`.

## Pruebas

### Pruebas automatizadas

```bash
php tests/localization_test.php
php tests/mailer_test.php
php -l index.php
php -l contact.php
php -l localization.php
php -l mailer.php
node --check assets/js/main.js
```

`localization_test.php` comprueba:

- Paridad de claves entre español e inglés.
- Terminología consistente en inglés estadounidense.
- Prioridades de `Accept-Language`.
- Países hispanohablantes y no hispanohablantes.
- Preferencia manual sobre navegador y país.

`mailer_test.php` levanta un servidor SMTP local simulado y valida:

- Remitente.
- Destinatario.
- `Reply-To`.
- Asunto.
- Cuerpo del mensaje.
- Escape de líneas que comienzan por punto.

La prueba SMTP simulada no envía correos reales.

### Comprobación manual del formulario

1. Abre el portfolio en una ventana privada.
2. Espera al menos dos segundos.
3. Completa todos los campos.
4. Acepta la casilla de privacidad.
5. Envía el mensaje.
6. Comprueba el estado de éxito.
7. Verifica la recepción en `PORTFOLIO_TO_EMAIL`.
8. Pulsa **Responder** y confirma que el destinatario es el correo indicado en el formulario.

Logs relevantes en caso de error:

```text
Portfolio contact form: MX delivery failed for ...
Portfolio contact form: all email transports rejected a message.
```

## Solución de problemas

### `Failed to listen on 127.0.0.1:8080`

El puerto está ocupado o reservado. Prueba otro:

```bash
php -S 127.0.0.1:8081
```

En Windows puedes comprobarlo con:

```powershell
Get-NetTCPConnection -LocalPort 8080 -ErrorAction SilentlyContinue
```

### `ERR_QUIC_PROTOCOL_ERROR`

QUIC y HTTP/3 se negocian entre el navegador y Cloudflare antes de llegar a PHP. Si HTTPS mediante HTTP/1.1 o HTTP/2 responde correctamente, normalmente se trata de un fallo temporal de red, navegador o Cloudflare y no de la aplicación.

Prueba:

- Recargar la página.
- Abrir una ventana privada.
- Cambiar de red.
- Desactivar temporalmente VPN, antivirus o inspección HTTPS.
- Revisar el estado de HTTP/3 en Cloudflare.

### El formulario devuelve un error `500`

Comprueba:

- `PORTFOLIO_TO_EMAIL` y `PORTFOLIO_FROM_EMAIL`.
- Resolución de los registros MX.
- Puerto saliente `25`.
- Logs del contenedor.
- SPF y reputación de la IP.

### Umami no registra visitas

Comprueba:

- Formato UUID de `UMAMI_WEBSITE_ID`.
- URL HTTPS correcta en `UMAMI_SCRIPT_URL`.
- Dominio incluido en `UMAMI_DOMAINS`.
- Que el navegador no tenga **Do Not Track** activado.
- Bloqueadores de publicidad o rastreo.
- Consola y pestaña **Network** del navegador.
- CSP devuelta por la página.

### Se muestra un idioma incorrecto

1. Abre `/?lang=es` o `/?lang=en`.
2. Comprueba la cookie `portfolio_lang`.
3. Revisa `Accept-Language`.
4. Comprueba las cabeceras de país del proxy.
5. Verifica que Cloudflare no esté cacheando el HTML sin respetar `Vary`.

## Lista de comprobación de producción

- [ ] Dominio y HTTPS funcionando.
- [ ] Redirección HTTP → HTTPS activada.
- [ ] Variables `PORTFOLIO_*` configuradas.
- [ ] Variables `UMAMI_*` configuradas si se requieren estadísticas.
- [ ] Resolución MX disponible.
- [ ] Conexiones TCP salientes al puerto `25`.
- [ ] SPF configurado.
- [ ] PTR o DNS inverso configurado.
- [ ] DMARC publicado.
- [ ] Formulario probado con un buzón real.
- [ ] `Reply-To` comprobado.
- [ ] Visitas y eventos visibles en Umami.
- [ ] Español e inglés revisados.
- [ ] Cabeceras CSP y seguridad verificadas.
- [ ] Reglas de caché compatibles con la selección de idioma.
- [ ] `sitemap.xml` enviado a buscadores.
- [ ] Caché de Cloudflare purgada después del despliegue.
- [ ] Auto Deploy de Coolify activado si se desea.

## Licencia

Código y recursos creados para el portfolio personal de Sergio Moreno García.

No se concede permiso para reutilizar las fotografías, el retrato generado, la identidad visual ni los elementos de marca sin autorización expresa.
