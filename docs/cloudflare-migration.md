# Astro y Cloudflare Pages

La aplicación estática genera 2 páginas del portfolio y 14 de SaveTempo. El código PHP anterior se conserva para recuperación; no entra en `dist`. Las páginas reutilizan los estilos, las imágenes, el JavaScript de navegación y el contenido de la versión publicada.

## Compatibilidad Android

Se revisaron `lib/core/product/product_info.dart`, `product_information_screen.dart` y `android/app/src/main/kotlin/es/sergiotech/savetempo/MainActivity.kt` del proyecto SaveTempo local. `ProductLinks` contiene:

- `https://sergiotech.es/apps/savetempo/`
- `https://sergiotech.es/apps/savetempo/privacy/`
- `https://sergiotech.es/apps/savetempo/support/`
- `https://sergiotech.es/apps/savetempo/terms/`

Android abre esos enlaces con un intent HTTPS externo. El código revisado no restringe el hostname de la redirección. El router conserva cada ruta mediante HTTP 301 hacia el mismo documento en `savetempo.sergiotech.es`. Conserva también variantes españolas, rutas `index.php`, idiomas por query y las guías. Se debe mantener esa compatibilidad indefinidamente: no exige que los usuarios instalen una actualización para leer los textos legales.

Esta revisión del código local no equivale a una prueba del APK instalado. Antes de cambiar DNS se comprueban los enlaces y la entrega de correo contra el despliegue nuevo. Las URL configuradas en Google Play pueden actualizarse después de validar el nuevo dominio; los enlaces antiguos seguirán resolviendo.

## Publicación y reversión

1. Publicar la rama de migración sin cambiar el dominio actual.
2. Compilar con `npm ci`, `npm run build` y `npm test` (Node 24).
3. Configurar secretos del contacto, KV y dominios permitidos de Turnstile.
4. Verificar español/inglés, SaveTempo, estilos, SEO, rutas legales y un mensaje entregado en Resend.
5. Asociar los dominios al proyecto Pages y cambiar los registros web, preservando los MX de OVH.
6. Desactivar Auto Deploy de Coolify antes de integrar cambios de Node en main. Retirar su contenedor únicamente tras verificar el nuevo alojamiento público.

Conservar el commit PHP `155b0a4549fe045b213b1fe1eff6f1ac435f7495` y los antiguos registros A a `87.106.25.136` para una reversión. Los secretos no pertenecen al repositorio.

## Rendimiento e indexación

El tema se fija antes de procesar los estilos para evitar repintados y transiciones de color durante la carga. El retrato AVIF usa precarga responsive. Turnstile y la sesión del formulario se inicializan al acercarse al formulario o enfocarlo; la validación del servidor permanece obligatoria. Los enlaces canónicos y hreflang distinguen español e inglés. `?lang=es` redirige a `/` y las rutas antiguas de SaveTempo redirigen al documento equivalente del nuevo subdominio. Las vistas pages.dev llevan noindex.

Search Console ya dispone de la propiedad de dominio sergiotech.es, que incluye el subdominio. Enviar ambos sitemaps tras el cambio; no utilizar Cambio de dirección para todo sergiotech.es, porque solo se traslada una sección. Las puntuaciones de PageSpeed son mediciones de laboratorio y no garantizan resultados de usuarios reales ni posicionamiento.

## Consumo de Functions y sondeos automatizados

El alojamiento estático de Pages tiene peticiones gratuitas e ilimitadas. Las Pages Functions comparten el límite gratuito de 100.000 invocaciones diarias, reiniciado a medianoche UTC, con los demás Workers de la cuenta. El total de peticiones de la zona en Analytics incluye estáticos, redirecciones y bloqueos; no equivale a invocaciones consumidas.

`scripts/finalize-pages.mjs` genera una lista de inclusión para `_routes.json`: portadas, idiomas, documentos de SaveTempo, redirecciones antiguas, sitemaps, robots y contacto. Las plantillas internas permanecen protegidas por el router. Las peticiones a rutas inexistentes, como `/.env.aws`, `/.git/config` o `/wp-login.php`, reciben el 404 estático de Pages sin ejecutar la Function. Los recursos CSS, JavaScript e imágenes también quedan fuera. Al añadir nuevas páginas que dependan del hostname o del idioma, hay que ampliar la lista; las rutas de producto proceden automáticamente de `src/data/product.json`.

Las reglas WAF y de frecuencia de la zona requieren registros web con proxy activo. Un bloqueo WAF puede seguir apareciendo como petición y código 403 en Analytics: su finalidad es impedir que llegue a la aplicación, no ocultarlo de las estadísticas. El límite de frecuencia gratuito no constituye un tope diario global y puede actuar con cierto retraso. No debe aplicarse indiscriminadamente a bots verificados ni a todos los recursos de una página.

Referencias: [precios de Pages Functions](https://developers.cloudflare.com/pages/functions/pricing/), [rutas de invocación](https://developers.cloudflare.com/pages/functions/routing/), [límites de frecuencia](https://developers.cloudflare.com/waf/rate-limiting-rules/).
