# Portfolio de Sergio Moreno García

Portfolio profesional de [Sergio Moreno García](https://sergiotech.es), desarrollado exclusivamente con HTML5, CSS3, JavaScript nativo y PHP.

El sitio presenta el perfil profesional, la experiencia laboral y los proyectos personales de Sergio mediante un diseño responsive, bilingüe y optimizado para buscadores y rendimiento. Incluye un formulario de contacto con entrega real de correo.

- Sitio web: [https://sergiotech.es](https://sergiotech.es)
- Repositorio: [sergineko/portfolio](https://github.com/sergineko/portfolio)
- Rama de producción: `main`

## Estado actual y últimas actualizaciones

- Diseño moderno con temas claro y oscuro.
- Retrato profesional personalizado y adaptado a pantallas grandes y móviles.
- Capturas reales y enlaces funcionales para MyWorkingArea y Vyrsea.
- Identidad visual propia, favicon e iconos PWA.
- Favicon de alta resolución declarado mediante la URL estable y específica `/sergiotech-favicon-192.png`, con `/favicon.ico` como compatibilidad.
- Imágenes responsive en AVIF y WebP para mejorar el LCP.
- Sitio completamente disponible en español e inglés estadounidense.
- Portada estable en español y variante inglesa en `/?lang=en`, con selector manual ES / EN.
- SEO, Open Graph, datos estructurados y mensajes accesibles traducidos.
- Formulario de contacto localizado con validación, medidas antispam y envío real.
- Entrega de correo sin credenciales SMTP obligatorias mediante `mail()` o conexión directa al MX.
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

- [SaveTempo](https://sergiotech.es/apps/savetempo/), micrositio oficial de un planificador local de hábitos de ahorro disponible para Android en Google Play; la versión de iOS aún no está publicada.
- [MyWorkingArea](https://myworkingarea.com/), disponible en producción.
- [Vyrsea](https://vyrsea.com/), en desarrollo y pruebas.
- Capturas reales, estados, descripciones y enlaces externos seguros.
- Carga diferida de las capturas para evitar penalizar la carga inicial.
- Enlaces HTML rastreables con nombres descriptivos y sin `nofollow`.
- Datos estructurados que identifican ambos dominios como sitios creados por Sergio.

### Relación entre dominios

`sergiotech.es` enlaza directamente a los dos proyectos mediante elementos `<a href>` presentes en el HTML generado en el servidor. La política `strict-origin-when-cross-origin` permite que el destino conozca el origen `sergiotech.es` sin enviar la ruta completa.

El JSON-LD contiene:

- Una entidad `ProfilePage` para el portfolio.
- Una entidad `Person` para Sergio Moreno García.
- Una entidad `WebSite` para el propio portfolio.
- Entidades `WebSite` para MyWorkingArea y Vyrsea.
- Relaciones `mentions` desde el portfolio hacia los proyectos.
- Relaciones `creator` desde cada proyecto hacia Sergio.

La arquitectura, configuración, procedencia de assets y portabilidad del micrositio de SaveTempo se documentan en [`docs/products/savetempo.md`](docs/products/savetempo.md).

Para completar una relación recíproca y natural, es recomendable añadir en MyWorkingArea y Vyrsea un enlace contextual hacia `https://sergiotech.es/`, por ejemplo en el pie de página o en una sección acerca del creador. No se deben crear páginas de enlaces ni intercambios masivos destinados únicamente a manipular posiciones.

### Idiomas

- Contenido completo en español e inglés estadounidense.
- Traducción de textos visibles, metadatos SEO, accesibilidad, datos estructurados y respuestas del formulario.
- Selector manual `ES / EN`.
- Cada URL pública mantiene su idioma aunque cambien el navegador, el país o las cookies.
- Las antiguas variantes `/?lang=es` redirigen permanentemente a `/`.
- SaveTempo dispone de rutas independientes para español e inglés.

### Formulario de contacto

- Envío asíncrono mediante `fetch`.
- Redirección HTTP como alternativa si JavaScript no está disponible.
- Respuestas y correos localizados en español o inglés.
- Renovación del token CSRF después de cada envío correcto.
- `Reply-To` configurado con el correo del visitante.
- Transporte local mediante `mail()` y entrega SMTP directa al servidor MX como alternativa.

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
├── favicon-192x192.png
├── favicon.ico
├── sergiotech-favicon-192.png
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
| `index.php` | Renderizado principal, idioma, SEO, CSP, sesión y token CSRF. |
| `localization.php` | Catálogos ES/EN, detección del navegador, país y preferencia guardada. |
| `contact.php` | Validación del formulario, antispam, respuestas JSON y composición del correo. |
| `mailer.php` | Transporte mediante `mail()`, resolución MX, SMTP directo y `STARTTLS`. |

## Ejecución local

Con PHP instalado:

```bash
php -S 127.0.0.1:8080
```

Después abre:

- Español: [http://127.0.0.1:8080/](http://127.0.0.1:8080/)
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


## Variables de entorno

Configuración de referencia:

```dotenv
PORTFOLIO_SITE_URL=https://sergiotech.es
PORTFOLIO_TO_EMAIL=smorgarc@sergiotech.es
PORTFOLIO_FROM_EMAIL=no-reply@sergiotech.es

```

| Variable | Obligatoria | Finalidad |
|---|---:|---|
| `PORTFOLIO_SITE_URL` | Recomendada | URL canónica usada por SEO, Open Graph y datos estructurados. |
| `PORTFOLIO_TO_EMAIL` | Recomendada | Buzón que recibe los mensajes del formulario. |
| `PORTFOLIO_FROM_EMAIL` | Recomendada | Remitente del servidor; debe pertenecer a un dominio autorizado. |

La aplicación utiliza valores adecuados para `sergiotech.es` cuando no se definen las variables `PORTFOLIO_*`. En producción se recomienda declararlas explícitamente.


## Selección de idioma

El portfolio publica dos URL canónicas: `/` siempre en español y `/?lang=en` siempre en inglés. Ambas incluyen enlaces `hreflang` recíprocos; `x-default` apunta a `/`. El selector enlaza directamente a esas URL. `/?lang=es`, las variantes no normalizadas y `/index.php` redirigen con HTTP 301, conservando el estado del formulario.

El idioma de la portada ya no depende de cookies, `Accept-Language` ni país. Las funciones de detección siguen disponibles para el endpoint de contacto cuando no recibe un idioma explícito. La respuesta incluye `Content-Language`.

SaveTempo utiliza `/apps/savetempo/` para inglés y `/es/apps/savetempo/` para español, con sus respectivas páginas y guías. Las URL antiguas con `?lang=` se normalizan mediante Nginx y, como alternativa, PHP. El formulario sigue usando sesiones y respuestas sin caché; no debe activarse **Cache Everything** para el HTML del portfolio.

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
- Grafo JSON-LD con `ProfilePage`, `Person` y los sitios web de los proyectos.
- URL canónica específica para cada idioma.
- Enlaces `hreflang` para `es`, `en` y `x-default`.
- `sitemap.xml` con 16 URL canónicas: las dos variantes del portfolio y las 14 de SaveTempo.
- `robots.txt`.
- Edad actual calculada dinámicamente.
- Enlaces externos rastreables con `noopener`, texto descriptivo y referencia de origen.
- Favicon de 192×192 píxeles y `.ico` con tamaños 16, 32 y 48 desde rutas raíz estables.

### Rendimiento

- CSS crítico integrado en la respuesta para evitar una solicitud bloqueante.
- JavaScript diferido.
- Sin fuentes externas ni frameworks.
- Retrato de alta calidad con máster de 1120 × 1400 píxeles y variantes responsive AVIF/WebP de 480, 800 y 1120 píxeles.
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
> El proveedor PHP de Nixpacks utiliza Nginx, por lo que `.htaccess` no se aplica. El archivo `nginx.template.conf` de la raíz se detecta automáticamente al reconstruir la aplicación. Incluye las redirecciones de SaveTempo, la normalización de `www`, errores 404 para rutas inexistentes y protección de archivos internos. Las redirecciones relativas conservan HTTPS cuando TLS termina en Coolify o Cloudflare. Las cabeceras generadas desde PHP, incluida la CSP, siguen funcionando.

### 3. Configurar dominio y HTTPS

1. Añade `https://sergiotech.es` en **Domains**.
2. Apunta el DNS del dominio a la IP del servidor.
3. Activa HTTPS.
4. Fuerza la redirección de HTTP a HTTPS.
5. No publiques un puerto del host; Coolify dirige el dominio al puerto interno.

### 4. Configurar variables

1. Abre **Environment Variables → Developer View**.
2. Copia las variables de la sección correspondiente.
3. Configúralas como variables de ejecución o **Runtime Variables**.
4. No guardes contraseñas ni secretos en Git.

### 5. Desplegar

1. Guarda la configuración.
2. Pulsa **Deploy**.
3. Verifica en los logs que la aplicación escucha en el puerto `80`.
4. Comprueba `/` en español y `/?lang=en` en inglés, incluso enviando otro idioma o cookie. `/?lang=es` debe devolver 301 hacia `/`. Comprueba `/sergiotech-favicon-192.png`, `/favicon.ico` y los recursos de `assets/`. Una petición `GET` directa a `/contact.php` debe responder `405`.
5. Envía un mensaje real desde el formulario.
6. Activa **Auto Deploy** si quieres publicar automáticamente cada push a `main`.

## Pruebas

### Pruebas automatizadas

```bash
php tests/localization_test.php
php tests/mailer_test.php
php tests/savetempo_microsite_test.php
php -l index.php
php -l contact.php
php -l localization.php
php -l mailer.php
node --check assets/js/main.js
```

Con PHP-FPM y Nginx sirviendo el proyecto en un entorno local o de pruebas:

```powershell
$env:SEO_TEST_BASE_URL = 'http://127.0.0.1:18080'
node tests/seo_http_test.mjs
```

La prueba HTTP comprueba idiomas estables, URL canónicas, `hreflang`, el sitemap, redirecciones de las rutas antiguas, errores 404 y el retorno del formulario sin JavaScript. Las peticiones POST omiten el token CSRF y se rechazan antes de intentar enviar correo.

El diagnóstico de Search Console y los pasos posteriores al despliegue se documentan en [`docs/seo/indexing-audit-2026-10-02.md`](docs/seo/indexing-audit-2026-10-02.md).

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

### Se muestra un idioma incorrecto

1. Abre `/` para español o `/?lang=en` para inglés.
2. Comprueba `Content-Language`, el atributo `lang` y la URL canónica.
3. Verifica que la aplicación tenga desplegada la versión actual.
4. Revisa que Cloudflare no esté cacheando el HTML o ignorando el parámetro de idioma.

### Google muestra un favicon antiguo

1. Comprueba que `/sergiotech-favicon-192.png` y `/favicon.ico` responden `200`.
2. Purga en Cloudflare la caché de ambas rutas y de la página principal.
3. Inspecciona `https://sergiotech.es/` en Google Search Console.
4. Solicita una nueva indexación de la página principal.
5. Espera al siguiente rastreo; Google puede tardar varios días o semanas en actualizar el icono.

No cambies `/sergiotech-favicon-192.png` en futuras versiones: Google recomienda que la URL declarada del favicon sea estable.

## Lista de comprobación de producción

- [ ] Dominio y HTTPS funcionando.
- [ ] Redirección HTTP → HTTPS activada.
- [ ] Variables `PORTFOLIO_*` configuradas.
- [ ] Resolución MX disponible.
- [ ] Conexiones TCP salientes al puerto `25`.
- [ ] SPF configurado.
- [ ] PTR o DNS inverso configurado.
- [ ] DMARC publicado.
- [ ] Formulario probado con un buzón real.
- [ ] `Reply-To` comprobado.
- [ ] Español e inglés revisados.
- [ ] Cabeceras CSP y seguridad verificadas.
- [ ] `/sergiotech-favicon-192.png` y `/favicon.ico` devuelven el icono propio.
- [ ] Página principal reenviada para indexación después de cambiar el favicon.
- [ ] Enlaces recíprocos naturales añadidos desde los proyectos si procede.
- [ ] Reglas de caché compatibles con la selección de idioma.
- [ ] `sitemap.xml` enviado a buscadores.
- [ ] Caché de Cloudflare purgada después del despliegue.
- [ ] Auto Deploy de Coolify activado si se desea.

## Licencia

Código y recursos creados para el portfolio personal de Sergio Moreno García.

No se concede permiso para reutilizar las fotografías, el retrato generado, la identidad visual ni los elementos de marca sin autorización expresa.
