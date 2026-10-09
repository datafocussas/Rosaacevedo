# rosaacevedo.com · Sitio oficial Rosa Alcaldesa 2027

Sitio público y panel administrable de la precandidatura de Rosa María Acevedo Jaramillo a la Alcaldía de Itagüí 2027. Laravel 11 + Filament 3 + MySQL, pensado para el hosting compartido de Hostinger.

El documento rector es [`docs/Especificacion_Sitio_Web_Rosa_Alcaldesa_2027.md`](docs/Especificacion_Sitio_Web_Rosa_Alcaldesa_2027.md). Las reglas de trabajo están en [`CLAUDE.md`](CLAUDE.md).

## Qué hay

| Parte | Dónde |
|---|---|
| Componentes Blade `x-ra.*` (boton, encabezado, banner, formulario-registro, consentimiento, ejes, buzon, selector-comuna, tarjeta-noticia, agenda, franja-redes, pie, rosa-raices) | `resources/views/components/ra/` |
| Páginas públicas y bloques del constructor | `resources/views/sitio/`, `resources/views/partials/bloques/` |
| Estilos: tokens + kit `ra-` + ajustes del sitio, Montserrat autoalojada | `resources/css/` |
| Formularios por pasos, origen, cookies (Alpine.js) | `resources/js/` |
| Panel `/admin` (Contenido, Ciudadanía, Sitio) | `app/Filament/` |
| Captación: deduplicación, consentimientos, token de pasos | `app/Services/RegistroCiudadano.php`, `app/Services/Consentimientos.php`, `app/Support/` |
| Outbox y sincronización con el CRM | `app/Services/Crm/`, `app/Jobs/SincronizarCrm.php`, `app/Contracts/CrmCliente.php` |
| Migraciones (25 tablas de `docs/schema-mysql.sql`) y semillas | `database/` |
| CI y despliegue | `.github/workflows/`, `deploy/` |

Rutas públicas: `/`, `/conoce-a-rosa`, `/manifiesto`, `/propuestas`, `/propuestas/{eje}`, `/comunas`, `/comunas/{slug}`, `/buzon`, `/sumate`, `/noticias`, `/agenda`, `/enlaces`, `/politica-de-datos`, `/mis-datos`, `/uso-de-ia`, `/transparencia` (solo modo campaña), `/q/{codigo}`, `/sitemap.xml`, `/robots.txt`. API en `/api/v1` (sección 05).

## Desarrollo local

Con Docker:

```bash
cp .env.example .env
docker compose up -d
docker compose exec app composer install
docker compose exec app php artisan key:generate
# CELULAR_HMAC_KEY: php -r "echo bin2hex(random_bytes(32));"  → pegar en .env
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan usuarios:crear tu@correo.co --nombre="Tu nombre" --rol=administrador
npm install && npm run dev
```

Sin Docker: PHP 8.3, Composer, Node 22 y MariaDB 10.11 (o MySQL 8). Mismos pasos con `php artisan serve`.

El sitio queda en `http://localhost:8000` y el panel en `/admin`. En local `DOS_FACTORES=false`; en pruebas y producción va en `true`.

### Comandos útiles

| Comando | Para qué |
|---|---|
| `php artisan test` | Pruebas (SQLite en memoria; en CI corren contra MariaDB 10.11) |
| `vendor/bin/pint` | Estilo de código |
| `php artisan usuarios:crear correo --rol=editor` | Crear usuarios del panel (administrador, editor, moderador, analista) |
| `php artisan territorio:importar barrios.csv --simular` | Validar y cargar el catálogo de barrios, sectores y veredas con su comuna 2024 y 2007 (formato en `docs/plantilla-territorio.csv`; sin `--simular` guarda). Mientras no se cargue, los formularios ocultan el selector de barrio |
| `php artisan sitio:publicar-programados` | Publica noticias y banners programados (corre cada minuto) |
| `php artisan schedule:work` | El cron en local |

Formato del CSV territorial (lo entrega el CRM):

```csv
nombre,tipo,comuna_2024,comuna_2007,codigo_externo
Santa María 1,barrio,C04,C04,C04-SM1
El Ajizal,vereda,CORR,CORR,CORR-AJ
```

## Despliegue en Hostinger

Requisitos del plan (sección 06): Business o Cloud Startup con SSH, cron, PHP 8.3 y MySQL. El servidor no necesita Composer ni Node: todo se construye en GitHub Actions.

### 1. Preparar Hostinger (una vez)

1. En hPanel: **Avanzado → PHP** en 8.3 para el dominio; extensiones `gd`, `intl`, `zip`, `exif`, `pdo_mysql`, `sodium` activas.
2. **Bases de datos → MySQL**: crear `uXXXX_rosa` y su usuario.
3. **Avanzado → Acceso SSH**: activar y agregar la llave pública del despliegue. El puerto suele ser `65002`.
4. Por SSH, ubicar el PHP 8.3 de la línea de comandos (`php -v`; si no es 8.3, usar la ruta completa, por ejemplo `/opt/alt/php83/usr/bin/php`).
5. Crear `~/rosaacevedo/shared/.env` a partir de `.env.example`, con:
   - `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://rosaacevedo.com`, `APP_KEY` (generar con `php artisan key:generate --show` en local).
   - `DB_HOST=localhost` y los datos de la base.
   - `CELULAR_HMAC_KEY` (32 bytes aleatorios en hexadecimal). **No cambiarla nunca después**: rompe la deduplicación.
   - `SESSION_SECURE_COOKIE=true`, `RESPONSE_CACHE_ENABLED=true`, `DOS_FACTORES=true`.
   - Turnstile, Umami, Brevo, `CRM_*` y `BACKUP_*` cuando estén.
6. Subir y ejecutar `deploy/primera-vez.sh ~/rosaacevedo ~/domains/rosaacevedo.com/public_html`. Crea `releases/`, `shared/storage` y deja `public_html` como enlace a `current/public`.
   Si hPanel no permite el enlace simbólico, usar `deploy/public_html.htaccess` (instrucciones dentro del archivo).
7. **Avanzado → Cron Jobs**, cada minuto:
   ```
   cd /home/uXXXX/rosaacevedo/current && /opt/alt/php83/usr/bin/php artisan schedule:run >> /dev/null 2>&1
   ```
   El programador vacía la cola cada minuto (`queue:work --stop-when-empty`), sincroniza el CRM, publica lo programado y hace las copias nocturnas. No hay procesos permanentes.

### 2. Configurar GitHub (una vez)

En **Settings → Environments** crear `produccion` (y `pruebas` si aplica) con estos secretos:

| Secreto | Ejemplo |
|---|---|
| `HOSTINGER_SSH_HOST` | `123.45.67.89` |
| `HOSTINGER_SSH_PORT` | `65002` |
| `HOSTINGER_SSH_USER` | `u123456789` |
| `HOSTINGER_SSH_KEY` | llave privada ed25519 del despliegue |
| `HOSTINGER_RUTA` | `/home/u123456789/rosaacevedo` |
| `HOSTINGER_PHP_BIN` | `/opt/alt/php83/usr/bin/php` |

Y la variable `URL_SITIO` (`https://rosaacevedo.com` o `https://pruebas.rosaacevedo.com`).

### 3. Desplegar

Cada push a `main` corre las pruebas contra MariaDB 10.11, construye (`composer install --no-dev`, `npm run build`), sube por `rsync` a `releases/AAAAMMDD-HHMMSS-sha/`, ejecuta `deploy/activar.sh` (migraciones, cachés de config, rutas, vistas y Filament), cambia el enlace `current` de forma atómica, limpia la caché de respuestas y verifica `/up`. Se conservan las últimas 5 versiones; `deploy/revertir.sh` vuelve a la anterior con un cambio de enlace (no revierte migraciones).

Primer usuario en el servidor: `cd ~/rosaacevedo/current && php artisan usuarios:crear correo@rosaacevedo.com --rol=administrador`. Configura el doble factor en su primer ingreso.

### 4. Cloudflare

DNS en Cloudflare con proxy activo, TLS **completo (estricto)** y certificado de origen o el SSL de Hostinger. Turnstile se crea en el mismo panel (llaves en el `.env`). El sitio confía en las cabeceras del proxy para obtener la IP real de cada consentimiento.

### 5. Entorno de pruebas

`pruebas.rosaacevedo.com` como subdominio en Hostinger con base separada, `APP_ENV=pruebas`, `PRUEBAS_USUARIO` y `PRUEBAS_CLAVE` (contraseña básica) y `noindex` automático.

## Decisiones y desviaciones para revisar

- **Respuesta del registro**: la sección 05 pide `201` si el celular es nuevo y `200` si ya existía. El código de estado distinto revela si un número ya está en la base, así que la API responde `201` en ambos casos con el mismo cuerpo. Confirmar.
- **Revocatoria y supresión** desde `/mis-datos`: se radican y se aplican desde el panel (acción «Aplicar retiro») después de verificar la identidad, para que nadie retire los datos de otra persona con solo escribir su número.
- **Contrato del CRM**: `HttpCrmCliente` implementa la propuesta de la sección 05 (`POST {CRM_URL}/ingesta/{entidad}` con `X-Firma` y `X-Idempotencia`). Mientras no llegue la especificación OpenAPI, `CRM_DRIVER=nulo` deja el outbox pendiente; al configurarlo se envía todo lo acumulado.
- **Contraste del lema**: el coral `#f45a43` del «me planto.» sobre crema da 2,97:1 (axe lo marca; el mínimo para texto grande es 3:1). Es una decisión de marca: Comunicaciones decide si se oscurece un poco o se espera el lettering en SVG.
- **CSP**: Alpine.js necesita `'unsafe-eval'`; no hay scripts en línea. Sin nonces, para que funcione con la caché de páginas completas.
- **Textos legales**: las semillas crean la versión `0.1` de cada texto como borrador técnico, para que los formularios funcionen. Antes de producción el jurídico aprueba la `1.0` y se activa en **Sitio → Políticas**.

## Pendientes de la campaña que bloquean la salida

Hoja de vida oficial (formación, cargos, periodos), manifiesto final, textos de los 7 ejes y validación del eje de salud, número de WhatsApp Business, responsable del tratamiento y textos legales aprobados, catálogo de 84 barrios + 6 sectores + 8 veredas con su correspondencia 2007, fotografías en alta, logotipo y lettering en SVG, especificación de la API del CRM y accesos a Hostinger y al DNS. En el sitio, todo dato no confirmado aparece con el marcador visible `[POR CONFIRMAR]`.
