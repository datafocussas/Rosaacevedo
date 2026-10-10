# Sitio oficial Rosa Alcaldesa 2027 · rosaacevedo.com

Sitio web oficial de la precandidatura de Rosa María Acevedo Jaramillo a la Alcaldía de Itagüí 2027. Lo desarrolla el equipo de tecnología de la campaña (líder: Yeyson Henao, DataFocus SAS). Todo el texto del sitio, el código visible, los commits y la comunicación con el equipo van en **español de Colombia**.

## Fuentes de verdad (leer antes de escribir código)

| Archivo | Qué define |
|---|---|
| `docs/Especificacion_Sitio_Web_Rosa_Alcaldesa_2027.md` | Manual de marca + secciones 01–08: mapa del sitio, requerimientos (RF-xx, RNF-xx), panel, modelo de datos, API, despliegue, cumplimiento legal, plan y criterios de aceptación. **Es el documento rector.** |
| `docs/schema-mysql.sql` | Modelo de datos de referencia (25 tablas, MySQL 8 / MariaDB 10.6+, probado en MariaDB 10.11) con datos semilla. Las migraciones de Laravel se escriben a partir de él. |
| `docs/diseno-v2/Diseno_Visual_v2_Noche_de_Raices.md` | Lenguaje visual vigente (v2 «Noche de raíces»): superficies, tipografía, componentes, rosa v3 y reglas para contenido del panel. Sus maquetas traen cifras sin confirmar: no copiarlas. |
| `docs/tokens.json` / `resources/css/tokens.css` | Tokens de diseño v2 (copia de `docs/diseno-v2/tokens-v2.css`). No escribir colores ni medidas a mano: usar `var(--token)`. |
| `resources/css/sitio.css` | Todos los estilos del sitio público (v2), clases con prefijo `ra-`. El kit v1 `ra-componentes.css` queda como histórico y ya no se importa. |
| `docs/componentes/*.html` | Referencia v1 de los componentes (histórico; para lo visual manda la v2). |
| `docs/Diseno_Sitio_Web.md` | Diseño **tal como está implementado**: tokens, reglas de color (verde = escucha y comunidad), componentes, entrada de inicio, rosa, bloques, accesibilidad y procedimientos para modificarlo. **Leerlo antes de cualquier cambio visual y actualizarlo en el mismo commit.** |
| `public/img/marca/` | Rosa «Aquí me planto» y raíces que usa el sitio (WebP optimizados a partir de las piezas de la campaña en `public/img/`). Ver §7 de `docs/Diseno_Sitio_Web.md`. |
| `docs/Guia_Paginas_y_Bloques.md` | Cómo se arma una página en el panel, qué hace cada bloque y a dónde va la información de los formularios. |

Si un documento anterior del proyecto (base técnica de agosto, plan de instrumentación) contradice la especificación, **manda la especificación**. En particular, el plan de instrumentación proponía Astro + PostgreSQL para el sitio público: quedó reemplazado por Laravel + MySQL en Hostinger.

## Stack decidido

- PHP 8.3, Laravel 11, Filament 3 (panel en `/admin`), Blade + Alpine.js en el sitio público, Vite.
- Base de datos: el MySQL del hosting de Hostinger. Cola, caché y sesiones con driver `database` (no hay Redis en hosting compartido).
- Paquetes: spatie/laravel-permission, spatie/laravel-activitylog, spatie/laravel-medialibrary (conversiones WebP), spatie/laravel-responsecache, spatie/laravel-backup, spatie/laravel-csp.
- Cloudflare delante (DNS, TLS estricto, Turnstile como antispam).
- Analítica: Umami autoalojado, sin cookies. Sin Google Analytics.
- Despliegue: GitHub Actions → rsync por SSH a `releases/` en Hostinger → enlace simbólico `current`. Cron de Hostinger cada minuto con `php artisan schedule:run`.
- Local: Docker Compose con PHP 8.3 + MariaDB 10.11.

## Convenciones

- Un componente Blade por componente del sistema: `<x-ra.boton>`, `<x-ra.encabezado>`, `<x-ra.banner>`, `<x-ra.formulario-registro>`, `<x-ra.consentimiento>`, `<x-ra.ejes>`, `<x-ra.buzon>`, `<x-ra.selector-comuna>`, `<x-ra.tarjeta-noticia>`, `<x-ra.agenda>`, `<x-ra.franja-redes>`, `<x-ra.pie>`, `<x-ra.rosa-raices>`.
- Tema de Filament con esmeralda (`#0f5c45`) como primario y Montserrat. Montserrat autoalojada en WOFF2 en producción.
- Nombres de tablas, columnas, modelos y rutas en español, como en `schema-mysql.sql`.
- Tono del sitio: tuteo («Súmate», «Cuéntanos»). Sin emojis. Sin «¡!» en titulares.
- Accesibilidad WCAG 2.1 AA: no quitar el anillo de foco, alto mínimo de controles 48 px, etiquetas asociadas, errores escritos (no solo color). El coral `#f45a43` nunca va en texto pequeño: usar `coral-texto`.

## Reglas que no se negocian

1. **No inventar datos biográficos ni cifras de Rosa.** Solo se publica lo que la campaña confirme con su fuente. Ya hubo un error («cuatro periodos como concejala» no es real). Si un texto necesita un dato no confirmado, dejar un marcador visible `[POR CONFIRMAR]`.
2. **Las cifras de la Encuesta Itagüí 2026 son internas.** No van en el sitio público.
3. **Un ciudadano = un registro**, deduplicado por celular (HMAC en `celular_hash`, valor cifrado en `celular_cifrado`). Nunca guardar el celular en texto plano.
4. **Consentimientos** (Ley 1581 de 2012, Circular SIC 002 de 2026): casillas separadas, nunca premarcadas; la general es obligatoria; WhatsApp y afinidad política son opcionales e independientes. La tabla `consentimientos` es de **solo inserción** (sin UPDATE ni DELETE), con versión del texto, IP, agente de usuario, formulario y hora en milisegundos.
5. **No pedir cédula** en el sitio. El puesto de votación solo en el paso 3 (voluntariado) y solo si la persona lo comparte.
6. **Modo precampaña**: no aparece «vota por» hasta que el panel cambie a modo campaña.
7. **División territorial**: siete comunas + corregimiento El Manzanillo (Acuerdo 017 de 2024) en todo el sitio público. Cada barrio guarda también su comuna de la división anterior (`comuna_2007_id`), porque las JAL de 2027 se eligen con esa.
8. El sitio **no envía mensajes masivos**: WhatsApp de salida y boletín los maneja el CRM. El sitio solo capta y sincroniza.
9. El formulario debe seguir captando aunque el CRM esté caído (tabla `crm_outbox` + reintentos).

## Redes oficiales (ya en los datos semilla)

- Facebook: https://www.facebook.com/share/19mmqrNJD9/ (enlace de compartir; reemplazar por la URL canónica cuando se tenga)
- Instagram: https://www.instagram.com/rosaacevedoj/
- TikTok: https://www.tiktok.com/@rosaacevedoj
- X: https://x.com/rosaacevedoj
- Canal de WhatsApp: https://whatsapp.com/channel/0029Vb6auV20QeanaeczZw2g

## Integración con el CRM

El CRM de la campaña vive en `https://aplicativo.rosaacevedo.co` (Laravel; documentación de API en `/docs/api`, hoy con acceso restringido, 403). La sincronización va detrás de `App\Contracts\CrmCliente` con el outbox. La conexión se configura en el panel (Sitio → Conexión con el CRM: URL por tipo de dato, API key cifrada y mapeo de campos; `App\Services\Crm\CrmConexion`); el envío es automático cada minuto. No inventar endpoints del CRM: la URL y los campos los pone la campaña según la documentación del CRM.

## Plan inmediato (sección 08)

- **Sprint 0 (6–10 oct 2026):** repositorio, CI/CD, verificación del plan de Hostinger (SSH, cron, PHP 8.3), esqueleto Laravel + Filament, tokens y CSS en Vite, componentes Blade, migraciones, semillas (comunas, ejes, redes, ajustes).
- **Sprint 1 (13–23 oct):** captación MVP (registro pasos 1–2, consentimientos, outbox), panel (banners, noticias, páginas, ejes, menú, redes, configuración), políticas, `/mis-datos`, `/q/` + QR, `/enlaces`, A/B del banner, Umami, Turnstile, redirecciones.
- **Salida a producción:** 24 oct 2026. Hito crítico: captar datos antes de terminar octubre.

## Pendientes de la campaña (no bloquean el código, sí la salida)

- Hoja de vida oficial de Rosa (formación, cargos, periodos) para «Conoce a Rosa».
- Logotipo y rotulado «Aquí me planto» en SVG (hoy el lema va en Montserrat 800 como sustituto).
- Fotografías definitivas en alta (la foto actual es un recorte de una pieza, solo para maquetas).
- Textos de los 7 ejes y validación del eje de salud con Estrategia.
- Número de WhatsApp Business de la campaña (distinto del canal).
- Responsable del tratamiento de datos (nombre, NIT/cédula) y textos legales aprobados.
- Catálogo corregido de 84 barrios + 6 sectores + 8 veredas con su correspondencia 2007.
- Especificación de la API del CRM.
- Datos del plan de Hostinger, acceso SSH y acceso al DNS de rosaacevedo.com.

## Estado del código y comandos

El esqueleto del Sprint 0 y la captación del Sprint 1 están en el repositorio (ver `README.md`). Antes de entregar cambios: `vendor/bin/pint`, `php artisan test` y `npm run build`. Decisiones abiertas y desviaciones de la especificación: sección «Decisiones y desviaciones para revisar» del `README.md`.
