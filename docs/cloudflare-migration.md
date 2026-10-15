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
