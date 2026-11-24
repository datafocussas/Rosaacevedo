# rosaacevedo.com · Sitio oficial Rosa Alcaldesa 2027

Sitio público y panel administrable de la precandidatura de Rosa María Acevedo Jaramillo a la Alcaldía de Itagüí 2027. Laravel 11 + Filament 3 + MySQL, pensado para el hosting compartido de Hostinger.

El documento rector es [`docs/Especificacion_Sitio_Web_Rosa_Alcaldesa_2027.md`](docs/Especificacion_Sitio_Web_Rosa_Alcaldesa_2027.md). Las reglas de trabajo están en [`CLAUDE.md`](CLAUDE.md).

## Qué hay

| Parte | Dónde |
|---|---|
| Componentes Blade `x-ra.*` (boton, encabezado, banner, formulario-registro, consentimiento, ejes, buzon, selector-comuna, tarjeta-noticia, tarjeta-columna, agenda, franja-redes, pie, rosa-raices, asistente) | `resources/views/components/ra/` |
| Páginas públicas y bloques del constructor | `resources/views/sitio/`, `resources/views/partials/bloques/` |
| Estilos: tokens + kit `ra-` + ajustes del sitio, Montserrat autoalojada | `resources/css/` |
| Formularios por pasos, carrusel de banners, chat del asistente, origen, cookies (Alpine.js) | `resources/js/` |
| Panel `/admin` (Contenido, Ciudadanía, Asistente, Sitio) | `app/Filament/` |
| Asistente virtual (Claude): reglas, base de conocimiento, conversaciones | `app/Services/Asistente/`, `app/Contracts/ModeloAsistente.php`, `docs/Guia_Asistente_Virtual.md` |
| Captación: deduplicación, consentimientos, token de pasos | `app/Services/RegistroCiudadano.php`, `app/Services/Consentimientos.php`, `app/Support/` |
| Outbox y sincronización con el CRM | `app/Services/Crm/`, `app/Jobs/SincronizarCrm.php`, `app/Contracts/CrmCliente.php` |
| Migraciones (25 tablas de `docs/schema-mysql.sql`) y semillas | `database/` |
| CI y despliegue | `.github/workflows/`, `deploy/` |

Rutas públicas: `/`, `/conoce-a-rosa`, `/manifiesto`, `/propuestas`, `/propuestas/{eje}`, `/comunas`, `/comunas/{slug}`, `/buzon`, `/sumate`, `/noticias`, `/opinion`, `/opinion/{slug}`, `/agenda`, `/enlaces`, `/politica-de-datos`, `/mis-datos`, `/transparencia` (solo modo campaña), `/q/{codigo}`, `/sitemap.xml`, `/robots.txt`. API en `/api/v1` (sección 05), más `POST /api/v1/asistente` (chat).

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

**Guía paso a paso para el equipo:** [`docs/Guia_Despliegue_Paso_a_Paso.md`](docs/Guia_Despliegue_Paso_a_Paso.md).

Resumen técnico: cada push a `main` (o «Run workflow» en Actions) corre las pruebas contra MariaDB 10.11, construye en el runner (`composer install --no-dev`, `npm run build`), sube por `rsync` a `releases/AAAAMMDD-HHMMSS-sha/` y por SSH ejecuta:

1. `deploy/preparar.sh`: la primera vez crea `shared/` y `shared/.env` (desde `.env.example`, con `APP_KEY` y `CELULAR_HMAC_KEY` generadas en el servidor; nunca lo sobrescribe) y enlaza `public_html` → `current/public` (guarda la carpeta anterior como respaldo). En cada despliegue ajusta `PRUEBAS_USUARIO`/`PRUEBAS_CLAVE` según los secretos `SITIO_USUARIO`/`SITIO_CLAVE` (sitio cerrado con contraseña y `noindex`; sin ellos, abierto).
2. `deploy/activar.sh`: migraciones, `db:seed` solo la primera vez (marca `shared/.datos-base-cargados`), primer administrador desde `PANEL_CORREO`/`PANEL_CLAVE` si no hay usuarios, cachés, cambio atómico de `current`, limpieza de caché de respuestas. Si algo falla, el sitio sale del modo mantenimiento y sigue la versión anterior. Conserva 5 versiones; `deploy/revertir.sh` vuelve a la anterior (no revierte migraciones).
3. `deploy/php.sh` ubica un PHP ≥ 8.3 (`HOSTINGER_PHP_BIN`, `/opt/alt/php83/usr/bin/php`, `php`).

Secretos del repositorio en GitHub (Settings → Secrets and variables → Actions; no se usan Environments): `HOSTINGER_SSH_HOST`, `HOSTINGER_SSH_PORT` (por defecto 65002), `HOSTINGER_SSH_USER`, `HOSTINGER_SSH_KEY`, `DB_NOMBRE`, `DB_USUARIO`, `DB_CLAVE`, `PANEL_CORREO`, `PANEL_CLAVE`, y opcionales `SITIO_USUARIO`, `SITIO_CLAVE`, `HOSTINGER_RUTA` (por defecto `~/rosaacevedo`), `HOSTINGER_PUBLIC_HTML` (por defecto `~/domains/{dominio}/public_html`), `HOSTINGER_PHP_BIN`. Variable: `URL_SITIO`.

Una vez en hPanel: PHP 8.3, base de datos, SSH con la llave pública y el cron cada minuto (el comando exacto queda en el resumen del despliegue en Actions). Turnstile, Umami, Brevo y copias externas se agregan después en `shared/.env`.

### 4. Cloudflare

DNS en Cloudflare con proxy activo, TLS **completo (estricto)** y certificado de origen o el SSL de Hostinger. Turnstile se crea en el mismo panel (llaves en el `.env`). El sitio confía en las cabeceras del proxy para obtener la IP real de cada consentimiento.

### 5. Entorno de pruebas

`pruebas.rosaacevedo.com` como subdominio en Hostinger con base separada, `APP_ENV=pruebas`, `PRUEBAS_USUARIO` y `PRUEBAS_CLAVE` (contraseña básica) y `noindex` automático.

## Decisiones y desviaciones para revisar

- **Respuesta del registro**: la sección 05 pide `201` si el celular es nuevo y `200` si ya existía. El código de estado distinto revela si un número ya está en la base, así que la API responde `201` en ambos casos con el mismo cuerpo. Confirmar.
- **Revocatoria y supresión** desde `/mis-datos`: se radican y se aplican desde el panel (acción «Aplicar retiro») después de verificar la identidad, para que nadie retire los datos de otra persona con solo escribir su número.
- **Contrato del CRM**: la conexión se configura en el panel (Sitio → Conexión con el CRM): URL para registros y para propuestas, autenticación (Bearer o encabezado propio) con la API key cifrada, y qué campos del sitio viajan con qué nombre. Cada minuto se envía lo pendiente del outbox (con `X-Idempotencia`) y la ficha de cada registro muestra si se envió o el error. Si el panel no está activo, sigue sirviendo la propuesta de la sección 05 por `.env` (`CRM_DRIVER=http`, `POST {CRM_URL}/ingesta/{entidad}` con `X-Firma`). El CRM debe actualizar el mismo contacto (por `uuid` o celular), porque un registro genera varios envíos.
- **Comuna obligatoria**: el paso 2 del registro y el buzón piden la comuna (7 comunas + corregimiento) aunque no haya catálogo de barrios; se guarda en `ciudadanos.comuna_id` y `propuestas_ciudadanas.comuna_id`.
- **Sin menciones de IA (10 oct 2026)**: la clasificación con IA (RF-22, fase 2) no está implementada, así que el sitio no la anuncia: se retiró el aviso del buzón, la página `/uso-de-ia` quedó archivada y fuera del menú, y la política de datos pasó a la versión 0.2 sin esa sección (la 0.1 queda como evidencia). El panel conserva el análisis de propuestas (estado, tema confirmado, asignación, respuesta) y el campo «Clasificación sugerida». Si se activa la IA, la Circular SIC 002 de 2026 obliga a volver a informarlo.
- **Asistente virtual (20 nov 2026)**: chat en todas las páginas con Claude (SDK oficial `anthropic-ai/sdk`), encendido desde **Asistente → Configuración** con la API key de Anthropic cifrada. Responde solo con la base de conocimiento del panel (y los ejes publicados), con reglas fijas en `App\Services\Asistente\Asistente::reglas()`: no inventar datos, no cifras de encuestas, no pedir el voto en precampaña, no pedir datos personales. Modelo por defecto Claude Opus 5.5 con esfuerzo bajo y respaldo automático del servidor si el modelo declina; se puede pasar a Sonnet 5.5 o Haiku 5.5. Límites: por conversación, por día (tope de gasto) y 8/min y 60/h por conexión. Conversaciones sin datos de contacto (IP solo como HMAC), borradas por `asistente:depurar` a los días de retención (90). En el sitio se presenta como «asistente automático»; **antes de encenderlo, el jurídico debe publicar una versión de la política de datos que lo incluya** (finalidad, retención, proveedor externo; Circular SIC 002 de 2026), y decidir si el aviso debe decir expresamente «inteligencia artificial».
- **Videos y redes incrustados (24 nov 2026)**: `App\Support\Incrustacion` convierte los enlaces pegados del editor (YouTube, Vimeo, Instagram, TikTok, X, Facebook) en el reproductor o la publicación, con iframes oficiales (sin scripts de terceros). Las redes se cargan con clic o con el permiso de cookies de redes sociales (Ley 1581 y sección 07). La CSP amplía `frame-src` a esas redes e `img-src` a `i.ytimg.com`. Bloque «Redes sociales: perfiles y publicaciones» y página `/en-redes` en borrador. El widget de la página de Facebook necesita la URL canónica de la página (hoy la semilla tiene un enlace de compartir). Los widgets de perfil de Instagram y TikTok usan los marcos públicos de cada red y pueden cambiar si la red los cambia.
- **Banners con varias fotos**: las colecciones `escritorio` y `movil` dejaron de ser de un solo archivo; las fotos rotan cada `segundos_rotacion` (3 a 30) con botón de pausa (WCAG 2.2.2).
- **Columnas de opinión**: tablas `columnistas` y `columnas`, mismo flujo editorial que noticias (programadas incluidas en `sitio:publicar-programados`). La migración agrega «Opinión» al pie en sitios que ya tienen menú.
- **Contraste del lema**: el coral `#f45a43` del «me planto.» sobre crema da 2,97:1 (axe lo marca; el mínimo para texto grande es 3:1). Es una decisión de marca: Comunicaciones decide si se oscurece un poco o se espera el lettering en SVG.
- **CSP**: Alpine.js necesita `'unsafe-eval'`; no hay scripts en línea. Sin nonces, para que funcione con la caché de páginas completas.
- **Textos legales**: las semillas crean la versión `0.1` de cada texto como borrador técnico, para que los formularios funcionen. Antes de producción el jurídico aprueba la `1.0` y se activa en **Sitio → Políticas**.

## Pendientes de la campaña que bloquean la salida

Hoja de vida oficial (formación, cargos, periodos), manifiesto final, textos de los 7 ejes y validación del eje de salud, número de WhatsApp Business, responsable del tratamiento y textos legales aprobados, catálogo de 84 barrios + 6 sectores + 8 veredas con su correspondencia 2007, fotografías en alta, logotipo y lettering en SVG, especificación de la API del CRM y accesos a Hostinger y al DNS. En el sitio, todo dato no confirmado aparece con el marcador visible `[POR CONFIRMAR]`.
