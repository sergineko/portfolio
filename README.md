# Portfolio de Sergio Moreno García

Portfolio profesional de [Sergio Moreno García](https://sergiotech.es), construido con HTML5, CSS3, JavaScript y PHP.

Incluye una presentación profesional, trayectoria laboral, proyectos, edad calculada dinámicamente, tema claro/oscuro y un formulario de contacto funcional con validación y medidas antispam.

## Tecnologías

- PHP 8.1 o superior.
- HTML5 semántico.
- CSS3 responsive, sin frameworks.
- JavaScript nativo, sin dependencias.
- Apache recomendado en producción.

## Estructura principal

```text
.
├── assets/
│   ├── css/styles.css
│   ├── icons/
│   ├── images/
│   └── js/main.js
├── .htaccess
├── contact.php
├── index.php
├── robots.txt
├── site.webmanifest
└── sitemap.xml
```

## Ejecución local

Con PHP instalado:

```bash
php -S 127.0.0.1:8080
```

Después abre [http://127.0.0.1:8080](http://127.0.0.1:8080).

El servidor integrado de PHP es adecuado para desarrollo, pero no debe utilizarse como servidor de producción.

## Despliegue desde Coolify

### 1. Crear la aplicación

1. Entra en el proyecto y entorno de Coolify.
2. Selecciona **New Resource**.
3. Si el repositorio es público, selecciona **Public Repository** y utiliza:

   ```text
   https://github.com/sergineko/portfolio
   ```

4. Si el repositorio es privado, conéctalo mediante **GitHub App** o **Deploy Key**.
5. Selecciona la rama `main`.

Coolify documenta ambos flujos en [Deploy Public Repository](https://coolify.io/docs/applications/ci-cd/github/public-repository) y en su sección de [Applications](https://coolify.io/docs/applications/).

### 2. Configurar la compilación

Utiliza estos valores:

| Opción | Valor |
|---|---|
| Build Pack | `Nixpacks` |
| Base Directory | `/` |
| PHP Root Directory | raíz del repositorio |
| Port Exposes | `80` |
| Health Check Path | `/` |
| Build Command | vacío |
| Start Command | vacío |

Nixpacks detecta automáticamente PHP porque existe `index.php`, configura Nginx y sirve la raíz del proyecto. Consulta el [proveedor PHP de Nixpacks](https://nixpacks.com/docs/providers/php).

> [!IMPORTANT]
> Nixpacks sirve PHP mediante Nginx, por lo que las reglas de `.htaccess` no se aplican. La página y el formulario funcionan, pero las cabeceras y políticas de caché declaradas exclusivamente en `.htaccess` deben trasladarse a una configuración `nginx.template.conf` si se quiere conservar exactamente ese comportamiento. Otra alternativa es desplegar mediante un Dockerfile basado en Apache.

### 3. Configurar el dominio

1. Añade `https://sergiotech.es` en el apartado **Domains**.
2. Apunta el registro DNS `A` o `AAAA` del dominio a la IP del servidor donde se ejecuta la aplicación.
3. Activa HTTPS y la redirección forzada a HTTPS.
4. No es necesario publicar un puerto del host: Coolify dirige el dominio al puerto interno `80`.

### 4. Variables de entorno

En **Environment Variables → Developer View**, añade:

```dotenv
PORTFOLIO_SITE_URL=https://sergiotech.es
PORTFOLIO_TO_EMAIL=smorgarc@sergiotech.es
PORTFOLIO_FROM_EMAIL=no-reply@sergiotech.es
```

Configúralas como variables de ejecución o **Runtime Variables**. No necesitan estar disponibles durante la compilación.

| Variable | Finalidad |
|---|---|
| `PORTFOLIO_SITE_URL` | URL canónica usada por el SEO y los datos estructurados. |
| `PORTFOLIO_TO_EMAIL` | Buzón que recibe los mensajes del formulario. |
| `PORTFOLIO_FROM_EMAIL` | Remitente utilizado por el servidor al enviar el mensaje. |

Coolify explica el funcionamiento de estas opciones en [Environment Variables](https://coolify.io/docs/knowledge-base/environment-variables).

### 5. Desplegar

1. Guarda la configuración.
2. Pulsa **Deploy**.
3. Comprueba en los logs que la aplicación escucha en el puerto `80`.
4. Visita el dominio y confirma que `/`, `/contact.php`, los recursos de `assets/` y `site.webmanifest` responden correctamente.
5. Si utilizas la integración GitHub App, puedes activar **Auto Deploy** para publicar automáticamente los siguientes commits de `main`.

## Configuración del envío de correos

### Funcionamiento actual

`contact.php` utiliza la función nativa `mail()` de PHP. El formulario establece:

- `From`: el valor de `PORTFOLIO_FROM_EMAIL`.
- `To`: el valor de `PORTFOLIO_TO_EMAIL`.
- `Reply-To`: el correo introducido por el visitante.

De esta forma se puede responder al visitante sin utilizar su dirección como remitente, lo que reduce rechazos por SPF o DMARC.

### Requisito imprescindible

Las tres variables `PORTFOLIO_*` configuran las direcciones, pero no configuran un transporte SMTP. El contenedor debe disponer de un agente compatible con `sendmail` o de un relay SMTP configurado para que `mail()` pueda entregar mensajes.

La configuración SMTP utilizada por las notificaciones internas de Coolify es independiente de las aplicaciones desplegadas. Configurarla en **Notifications** no hace que PHP pueda enviar correo automáticamente.

Antes de considerar terminado el despliegue, abre la terminal del contenedor desde Coolify y comprueba:

```bash
php -r 'var_dump(function_exists("mail"));'
php -r 'var_dump(ini_get("sendmail_path"));'
```

El primer comando debe devolver `true`. El segundo debe mostrar el ejecutable de transporte configurado. Si está vacío o el binario no existe, será necesario añadir un relay compatible con `sendmail` a la imagen de ejecución o adaptar `contact.php` para enviar mediante un proveedor SMTP.

> [!WARNING]
> No añadas variables como `SMTP_PASSWORD` pensando que el código actual las utilizará. Esta versión solo lee `PORTFOLIO_SITE_URL`, `PORTFOLIO_TO_EMAIL` y `PORTFOLIO_FROM_EMAIL`.

### Recomendaciones de entregabilidad

- Utiliza un remitente del propio dominio, por ejemplo `no-reply@sergiotech.es`.
- Autoriza al proveedor de correo en el registro SPF del dominio.
- Activa y valida DKIM.
- Publica una política DMARC.
- No utilices el correo del visitante como cabecera `From`.
- Revisa inicialmente la carpeta de spam y los logs del relay.
- No guardes contraseñas SMTP ni claves privadas en Git.

La función `mail()` puede devolver `true` cuando el transporte ha aceptado el mensaje, pero esto no garantiza que el servidor destinatario lo entregue.

### Prueba final

1. Abre el portfolio en una ventana privada.
2. Completa todos los campos y acepta la casilla de privacidad.
3. Envía un mensaje con una dirección real.
4. Confirma que aparece el mensaje de éxito.
5. Comprueba la recepción en `PORTFOLIO_TO_EMAIL`.
6. Pulsa **Responder** y verifica que el destinatario sea el correo introducido en el formulario.
7. Si falla, revisa los logs de la aplicación. El endpoint registra:

   ```text
   Portfolio contact form: mail transport rejected a message.
   ```

## Seguridad del formulario

El endpoint incluye:

- Token CSRF por sesión.
- Cookies `HttpOnly`, `SameSite=Lax` y `Secure` bajo HTTPS.
- Validación del origen de la petición.
- Campo trampa anti-spam.
- Tiempo mínimo antes del envío.
- Límite de un mensaje por minuto y sesión.
- Límites de longitud y tamaño de la petición.
- Protección frente a inyección de cabeceras.
- Respuestas JSON para JavaScript y redirección como alternativa.

## SEO y rendimiento

- Metadatos SEO y Open Graph.
- Datos estructurados `Person` mediante JSON-LD.
- Edad calculada dinámicamente.
- Imágenes adaptables en AVIF y WebP.
- Retrato principal priorizado para mejorar el LCP.
- CSS integrado en la respuesta inicial.
- Favicon, iconos PWA, `robots.txt` y `sitemap.xml`.
- Caché prolongada para recursos versionados cuando se utiliza Apache.

## Lista de comprobación de producción

- [ ] Dominio y HTTPS funcionando.
- [ ] Variables `PORTFOLIO_*` configuradas como Runtime Variables.
- [ ] Transporte de `mail()` disponible dentro del contenedor.
- [ ] SPF, DKIM y DMARC validados.
- [ ] Formulario probado con un buzón real.
- [ ] Cabeceras de seguridad y caché verificadas.
- [ ] `sitemap.xml` enviado a los buscadores.
- [ ] Caché de Cloudflare purgada después del despliegue.

## Licencia

Código y recursos creados para el portfolio personal de Sergio Moreno García. No se concede permiso para reutilizar las fotografías o la identidad visual sin autorización.
