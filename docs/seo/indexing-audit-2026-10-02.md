# Diagnóstico de indexación de sergiotech.es

Revisión realizada el 2 de octubre de 2026 sobre la propiedad de dominio de Google Search Console y las respuestas HTTP de producción. Los cambios de código descritos aquí están preparados localmente y pendientes de despliegue.

## Qué significan las 18 URL sin indexar

El informe de cobertura, actualizado al **21 de septiembre de 2026**, muestra una URL indexada y estas 18 exclusiones:

| Motivo | URL | Diagnóstico |
|---|---|---|
| Descubierta: actualmente sin indexar | Las 14 páginas de SaveTempo en español e inglés | Google las conoce en el informe, pero no registra un rastreo. Todas responden con HTTP 200, canonical propio y permiso de indexación. |
| Rastreada: actualmente sin indexar | `https://sergiotech.es/?lang=es` | Duplica la portada española en condiciones habituales; último rastreo registrado el 26 de julio. Se consolida en `/`. |
| Rastreada: actualmente sin indexar | `https://sergiotech.es/apps/savetempo/support?lang=es` | URL antigua que mantiene una variante duplicada. Último rastreo registrado el 11 de septiembre. Se redirige a `/es/apps/savetempo/support/`. |
| Duplicada: Google ha elegido una versión canónica diferente a la del usuario | `https://sergiotech.es/?lang=en` | La inspección confirma que Google eligió `https://sergiotech.es/` como canonical en el rastreo del 19 de septiembre. La portada variable también puede servir inglés, lo que crea señales contradictorias. |
| Página con redirección | `http://sergiotech.es/` | Exclusión correcta: redirige a HTTPS. No debe intentarse indexar esta variante HTTP. |

Las 14 URL pendientes son las dos versiones de cada página de SaveTempo: portada, privacidad, soporte, términos, reto de 52 semanas, reto de 365 días y aplicación de retos de ahorro. Están detalladas, junto con las comprobaciones de otras variantes, en [live-http-audit-2026-10-02.json](live-http-audit-2026-10-02.json).

El sitemap fue leído correctamente el **28 de septiembre**, con **17 URL descubiertas**. No hay un error de acceso al sitemap. Durante esta revisión, la inspección individual de ambas portadas de SaveTempo indicó «Google no reconoce esta URL», sin fecha de rastreo ni sitemap de referencia, aunque el informe general las enumera como descubiertas. Estas vistas no coinciden en la información disponible; no se interpreta como un bloqueo técnico del servidor.

Las estadísticas de rastreo, actualizadas al **30 de septiembre**, registran **349 solicitudes en 90 días**, una respuesta media de **528 ms** y menos del **1 % de respuestas 5xx**. El estado del host informa de **problemas históricos de conectividad**, con un porcentaje de error aceptable actualmente; DNS y robots.txt también tienen un porcentaje de error aceptable. Los incidentes anteriores podrían haber reducido el ritmo de rastreo, pero el informe no demuestra que sean la causa de las 14 URL pendientes. Conviene comprobar en los logs del origen que no vuelvan a producirse, especialmente durante despliegues.

## Problemas comprobados en producción

1. `/` cambia entre español e inglés según `Accept-Language`, cookie y país, conservando el mismo canonical. Se verificó con peticiones en ambos idiomas. Esto explica un conflicto real entre la portada y `/?lang=en`; no demuestra por sí solo el motivo de las 14 URL pendientes de rastreo.
2. `.htaccess` contiene las redirecciones de las URL antiguas, pero el despliegue PHP de Nixpacks utiliza Nginx y no lee ese archivo. La URL `/apps/savetempo/?lang=es` sigue devolviendo HTTP 200 como duplicado de `/es/apps/savetempo/`.
3. `/apps/savetempo/support?lang=es` hace dos saltos: primero a **HTTP** con barra final y después vuelve a HTTPS. Termina mostrando una URL antigua con canonical a la ruta española limpia.
4. `https://www.sergiotech.es/` y `/index.php` devuelven HTTP 200 como variantes de la portada. Es preferible consolidarlas mediante redirecciones.
5. La tarjeta de SaveTempo de la portada española enlaza a la portada inglesa del producto. Esto reduce el enlace directo desde la URL indexada a la versión española.

No se encontraron bloqueos `noindex`, cabeceras `X-Robots-Tag` restrictivas ni errores HTTP en las 17 URL del sitemap existente. Las comprobaciones directas desde esta conexión no sustituyen la inspección de Googlebot; las solicitudes de indexación aceptadas aportan una comprobación adicional de disponibilidad para Google.

## Correcciones preparadas

- `/` muestra siempre español; `/?lang=en` muestra siempre inglés. El usuario eligió expresamente este comportamiento.
- Canonical, `hreflang`, selector ES/EN y sitemap coinciden con esas dos URL.
- `/?lang=es` y `/index.php` redirigen con HTTP 301. Se conserva el estado del formulario y el inglés cuando corresponde.
- El sitemap contiene **16 URL canónicas**: dos del portfolio y 14 de SaveTempo. Se elimina el duplicado español de la portada.
- `nginx.template.conf`, detectado automáticamente por Nixpacks, normaliza las variantes antiguas de SaveTempo y `www`, mantiene HTTPS mediante redirecciones relativas y devuelve 404 para rutas inexistentes.
- PHP normaliza también las URL antiguas con idioma y los accesos directos a `index.php`, incluso sin las reglas de Apache.
- La portada española enlaza directamente al SaveTempo español; la inglesa, al inglés.
- El retorno del formulario sin JavaScript conserva el idioma en la URL canónica.

La configuración de Nginx incluye además protección para archivos internos, equivalente al propósito de las reglas de Apache. La plantilla respeta `NIXPACKS_PHP_ROOT_DIR`; no necesita nuevas variables en Coolify.

## Trabajo ya realizado en Search Console

Google aceptó solicitudes de indexación para:

- `https://sergiotech.es/es/apps/savetempo/`
- `https://sergiotech.es/apps/savetempo/`

Ambas se añadieron a la cola de rastreo prioritaria. Las confirmaciones están en [la captura española](savetempo-indexing-request-2026-10-02.jpg) y [la inglesa](savetempo-en-indexing-request-2026-10-02.jpg). Esto confirma la aceptación de la solicitud, **no que las páginas ya estén indexadas**.

No se reenviaron el sitemap ni las solicitudes de las portadas del portfolio antes del despliegue: Google todavía vería la configuración anterior. Tampoco se intentó validar como error la redirección HTTP correcta.

## Validación local

- Pruebas existentes de idiomas: correctas en PHP 8.3.
- Pruebas existentes de SaveTempo: correctas en PHP 8.3.
- Sintaxis de la configuración Nginx: correcta.
- **82 comprobaciones HTTP** correctas con PHP-FPM 8.3 y Nginx en una red Docker aislada. Incluyen las 16 páginas canónicas, idioma estable ante cookies y cabeceras contrarias, enlaces `hreflang`, variantes antiguas, barras finales, `www`, errores 404 y retorno del formulario sin enviar correo.

La prueba usa la plantilla con sus variables resueltas para el entorno Docker. La selección de la plantilla por la versión de Nixpacks instalada en Coolify debe confirmarse en los logs del siguiente despliegue.

## Pasos para completar la publicación

1. Publicar estos cambios en la rama de producción y **reconstruir** la aplicación en Coolify para que Nixpacks utilice `nginx.template.conf`.
2. Comprobar `/` con cookies e idioma inglés: debe devolver español. `/?lang=en` debe devolver inglés. Las 16 URL del sitemap deben responder 200 con canonical propio.
3. Comprobar `/apps/savetempo/support?lang=es`: debe devolver un único 301 hacia `/es/apps/savetempo/support/`, sin salto a HTTP.
4. Reenviar `https://sergiotech.es/sitemap.xml` en Search Console cuando producción publique las 16 URL canónicas.
5. Inspeccionar `/` y `/?lang=en`, probar las URL publicadas y solicitar indexación después de verificar el contenido corregido. Priorizar después las guías de SaveTempo si siguen sin rastrearse. Evitar repetir solicitudes de la misma URL mientras siga en cola.
6. Revisar la inspección individual y el informe de indexación tras el nuevo rastreo. Verificar que no se repitan los problemas históricos de conectividad. Si las URL siguen sin rastrearse, consultar estadísticas de rastreo y logs de servidor/Cloudflare para detectar errores 403, 429 o 5xx reales de Googlebot. Si se rastrean pero no se indexan, revisar calidad, diferencias de contenido, canonical elegido y enlaces entrantes pertinentes.

El resultado esperado es que Google pueda indexar las **16 páginas canónicas**. Las URL HTTP, antiguas o duplicadas seguirán pudiendo figurar como excluidas correctamente. Google decide la indexación y no ofrece una garantía de plazo ni de cobertura total.

## Fuentes oficiales

- [Interpretación del informe de indexación de páginas](https://support.google.com/webmasters/answer/7440203?hl=es): las páginas importantes deben poder indexarse; las alternativas y redirecciones pueden estar excluidas correctamente.
- [Gestión de sitios multilingües](https://developers.google.com/search/docs/specialty/international/managing-multi-regional-sites?hl=es): utilizar URL diferentes y evitar que el idioma de una misma URL dependa del navegador o las cookies.
- [Consolidación de URL duplicadas](https://developers.google.com/search/docs/crawling-indexing/consolidate-duplicate-urls?hl=es): alinear redirecciones, canonical y sitemap.
- [Proveedor PHP de Nixpacks](https://nixpacks.com/docs/providers/php): uso de Nginx y detección de `nginx.template.conf` en la raíz.
- [Solicitud de un nuevo rastreo](https://developers.google.com/search/docs/crawling-indexing/ask-google-to-recrawl?hl=es): las solicitudes no garantizan la indexación.
