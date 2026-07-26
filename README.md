# Portfolio de Sergio Moreno García

Portfolio profesional construido exclusivamente con HTML5, CSS3, JavaScript y PHP.

## Requisitos

- PHP 8.1 o superior.
- Un servidor web con transporte de correo configurado para la función `mail()`.
- HTTPS en producción.
- Apache con `mod_headers`, `mod_deflate` y `mod_expires` recomendado.

## Configuración de producción

La web funciona con valores seguros por defecto para `sergiotech.es`. Si el despliegue usa otro dominio o buzón, configura estas variables de entorno en el alojamiento:

```text
PORTFOLIO_SITE_URL=https://sergiotech.es
PORTFOLIO_TO_EMAIL=smorgarc@sergiotech.es
PORTFOLIO_FROM_EMAIL=no-reply@sergiotech.es
```

El dominio de `PORTFOLIO_FROM_EMAIL` debe tener SPF, DKIM y DMARC correctamente configurados para maximizar la entrega de los mensajes.

## Puesta en marcha local

Con PHP instalado:

```text
php -S 127.0.0.1:8080
```

Después, abre `http://127.0.0.1:8080`.

## Formulario de contacto

El endpoint `contact.php` incluye:

- Token CSRF.
- Campo trampa anti-spam.
- Límite temporal por sesión.
- Validación de origen, tamaño y todos los campos.
- Protección frente a inyección de cabeceras.
- Respuesta JSON progresiva y alternativa sin JavaScript.

Para que el correo salga en producción, el alojamiento debe tener habilitada y configurada la función PHP `mail()`.
