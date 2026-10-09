# Diseño del sitio web · rosaacevedo.com

Documento técnico del diseño **tal como está implementado**. Sirve como insumo antes de cualquier modificación visual: dice qué existe, dónde vive, por qué es así y cómo cambiarlo sin romper la marca, la accesibilidad ni el rendimiento.

| | |
|---|---|
| Versión | 1.0 · 9 de octubre de 2026 |
| Responsable | Tecnología (DataFocus SAS) |
| Alcance | Sitio público (Blade + Alpine.js) y tema del panel `/admin` (Filament 3) |
| Se actualiza | En el mismo commit que cambie el diseño (ver §17) |

## Índice

1. [Jerarquía de fuentes de verdad](#1-jerarquía-de-fuentes-de-verdad)
2. [Arquitectura de estilos](#2-arquitectura-de-estilos)
3. [Tokens de diseño](#3-tokens-de-diseño)
4. [Reglas de uso del color](#4-reglas-de-uso-del-color)
5. [Tipografía](#5-tipografía)
6. [Retícula, espaciado y puntos de quiebre](#6-retícula-espaciado-y-puntos-de-quiebre)
7. [Iconografía](#7-iconografía)
8. [Ilustración «Aquí me planto» (RosaRaices)](#8-ilustración-aquí-me-planto-rosaraices)
9. [Componentes Blade](#9-componentes-blade)
10. [Páginas y su composición](#10-páginas-y-su-composición)
11. [Bloques del constructor de páginas](#11-bloques-del-constructor-de-páginas)
12. [Interacción (Alpine.js)](#12-interacción-alpinejs)
13. [Imágenes y medios](#13-imágenes-y-medios)
14. [Accesibilidad](#14-accesibilidad)
15. [Panel administrable](#15-panel-administrable)
16. [Rendimiento y caché: efectos sobre los cambios de diseño](#16-rendimiento-y-caché-efectos-sobre-los-cambios-de-diseño)
17. [Cómo modificar el diseño (procedimientos)](#17-cómo-modificar-el-diseño-procedimientos)
18. [Decisiones registradas y pendientes](#18-decisiones-registradas-y-pendientes)

---

## 1. Jerarquía de fuentes de verdad

| Orden | Fuente | Qué manda |
|---|---|---|
| 1 | `docs/Especificacion_Sitio_Web_Rosa_Alcaldesa_2027.md` (manual de marca, al inicio) | Concepto, voz, reglas de color, tipografía, «lo que no se hace» |
| 2 | `docs/tokens.json` → `resources/css/tokens.css` | Valores exactos (colores, tipos, espacios, radios, sombras, medidas) con su contraste verificado |
| 3 | `resources/css/ra-componentes.css` | Kit base de clases `ra-` |
| 4 | Este documento | Cómo se aplicó todo lo anterior en el sitio real y los cambios posteriores |
| 5 | `docs/componentes/*.html` | Vistas de referencia **originales**. Ya no reflejan: el verde ampliado, la nueva rosa, la entrada con foto ni el aviso de escucha. No usarlas como verdad para esos puntos |

Si este documento y el código difieren, manda el código y este documento se corrige.

## 2. Arquitectura de estilos

### 2.1 Archivos

| Archivo | Rol | ¿Se edita? |
|---|---|---|
| `resources/css/tokens.css` | Variables CSS en `:root` generadas de `tokens.json` | Solo si cambia un token (ver §17.1) |
| `resources/css/ra-componentes.css` | Kit de la marca: tipografía, botones, encabezado, banner, tarjetas, ejes, formularios, comunas, agenda, redes, pie, cifras, panel, rosa | Evitarlo. Los ajustes van en `sitio.css` para conservar el kit como referencia. Única modificación hecha: se quitó el `@import` de Google Fonts |
| `resources/css/fuentes.css` | `@font-face` de Montserrat (400, 500, 700, 800) y Permanent Marker, WOFF2 subconjunto latino desde `@fontsource` | Solo si cambia la tipografía |
| `resources/css/sitio.css` | Punto de entrada. Importa los tres anteriores y agrega la maquetación del sitio y las extensiones de marca | **Aquí van los cambios de diseño** |
| `public/css/montserrat-panel.css` + `public/css/fuentes/` | Montserrat autoalojada para el panel Filament | Solo si cambia la tipografía |

### 2.2 Orden de carga

`sitio.css` → `fuentes.css` → `tokens.css` → `ra-componentes.css` → reglas propias de `sitio.css`. Las reglas de `sitio.css` ganan sobre el kit por orden, con la misma especificidad. Por eso las extensiones de marca (verde en comunas y agenda, entrada con foto) están al final de `sitio.css` y no en el kit.

### 2.3 Secciones de `sitio.css`

Utilidades de composición · Saltar al contenido · Encabezado · Banner · Formularios · Tarjetas · Agenda · Redes y botones · Contenido largo (`ra-prosa`) · Línea de tiempo · Video · Compromisos del eje · Enlaces · Paginación · Cookies · Tabla simple · Páginas · **Verde: escucha y comunidad** · Comunas con acento verde · Agenda con fechas verdes · Franja «Raíces» · **Rosa «Aquí me planto»** · **Entrada de inicio con foto**.

### 2.4 Reglas

- Todo color, espacio, radio y sombra sale de `var(--token)`. Las únicas excepciones son: `errors/500.blade.php` y `errors/503.blade.php` (deben funcionar sin Vite ni base de datos), los colores fijos del SVG de marca autónomo `public/img/rosa-raices.svg` y del tema del panel (`AdminPanelProvider`, que recibe valores hexadecimales).
- Prefijo `ra-` para toda clase nueva. Nombres en español.
- Sin estilos en línea en las vistas públicas (la CSP permite `style-src 'unsafe-inline'`, pero se evita por mantenimiento).
- Sin degradados, sin emojis, sin tarjetas con borde lateral de color (manual de marca).
- Compilación: `npm run build` (Vite). En Docker local: `docker run --rm -v "$PWD":/app -w /app node:22 sh -c "npm install && npm run build"`.

## 3. Tokens de diseño

### 3.1 Color

| Token | Valor | Uso en el sitio | Contraste clave |
|---|---|---|---|
| `azul-itagui` | `#003c57` | Titulares, navegación, botón secundario, franja de redes, tierra de la rosa, anillo de foco | 10,7:1 sobre crema |
| `azul-profundo` | `#002536` | Pie de página | crema encima 14,5:1 |
| `azul-medio` | `#0e5674` | Hover del botón secundario, línea del pie legal | — |
| `azul-claro` | `#c9d6dd` | Texto secundario sobre azules, montañas de la rosa (55 % de opacidad) | 10,7:1 sobre azul-profundo |
| `azul-tenue` | `#e3edf2` | Chips por defecto, casilla opcional de consentimiento, hover de botón fantasma | azul-itagui encima 9,9:1 |
| `coral` | `#f45a43` | «ME PLANTO», íconos circulares de ejes, pétalos de la rosa, subrayado del menú activo | 3,0:1 sobre crema: **solo piezas ≥ 24 px** |
| `coral-texto` | `#c23a24` | Botón principal, enlaces, «ROSA» de la marca, barra de paso activo | blanco encima 5,4:1; 4,9:1 sobre crema |
| `coral-oscuro` | `#a32e1b` | Hover del botón principal, sombras de la rosa | — |
| `coral-claro` | `#ff8a75` | Rótulos sobre azul, brillos de los pétalos, foco inverso | 5,1:1 sobre azul-itagui |
| `coral-tenue` | `#fde6e1` | Fondo de aviso de error (`ra-aviso-error`) y chip coral | tinta encima 13,6:1 |
| `crema` | `#f8f3ef` | Fondo general, tarjeta del titular en la entrada, botón claro | — |
| `blanco` | `#ffffff` | Tarjetas, formularios, campos | — |
| `tinta` | `#13222b` | Texto de cuerpo | 14,8:1 sobre crema |
| `tinta-suave` | `#4a5a64` | Texto secundario, fechas, ayudas | 6,5:1 sobre crema |
| `borde-control` | `#8a8178` | Borde de campos y casillas | 3,5:1 (no textual) |
| `borde-suave` | `#ddd5cc` | Separadores decorativos | decorativo |
| `verde-raiz` | `#2f7d5b` | Tallo, hojas y espinas de la rosa; ícono del aviso de escucha; hover de comunas; ícono de lugar en agenda | ≈ 4,5:1 sobre crema (texto mínimo AA) |
| `verde-profundo` | `#1f5a40` | Raíces y hoja oscura de la rosa; franja del aviso de escucha; fecha de agenda; número de comuna; rótulos y títulos sobre verde-tenue | 7,3:1 sobre crema; crema encima ≈ 7,3:1 |
| `verde-tenue` | `#e2f0e8` | Franja «Raíces», llamado al buzón, chips de tema, avisos de éxito, hover de comunas | verde-profundo encima 6,9:1 |
| `alerta` / `alerta-tenue` | `#9a5b00` / `#fbf0dc` | Avisos de advertencia; marcador `[POR CONFIRMAR]` | 4,9:1 |
| `error` | `#a4231b` | Mensajes y bordes de error | 6,7:1 |
| `foco` / `foco-inverso` | azul-itagui / coral-claro | Anillo de foco sobre claro / sobre azul o verde profundo | — |

### 3.2 Tipografía

| Estilo | Clase | Tamaño / interlínea | Peso | Uso |
|---|---|---|---|---|
| display-xl | `ra-display-xl` | 40/44 móvil, 56/60 desde 768 px | 800 | Titular del banner. En la entrada con foto (escritorio) se fija en 44/48 |
| display | `ra-display` | 40/44 | 800 | Títulos de página (`h1`) |
| h2 | `ra-h2` | 30/36 | 700 | Títulos de sección |
| h3 | `ra-h3` | 22/28 | 700 | Tarjetas, subsecciones, título del formulario |
| cuerpo-lg | `ra-cuerpo-lg` | 18/28 | 400 | Entradillas |
| cuerpo | (base de `.ra`) | 16/26 | 400 | Texto corrido; nunca menos de 16 px en móvil |
| pequeño | `ra-pequeno` | 14/20 | 500 | Fechas, ayudas, notas legales |
| etiqueta | `ra-etiqueta` | 13/16, `letter-spacing: .08em`, mayúsculas por CSS | 700 | Rótulos («POR EL FUTURO DE ITAGÜÍ») |
| lema | `ra-lema` | 34/38 móvil, 48/52 desde 768 px, rotado −3° | 400 | Solo «Aquí me planto.» y el hashtag |
| prosa | `ra-prosa` | 17/28; h2 26/32; h3 21/28 | — | Cuerpo de noticias, páginas y políticas |

### 3.3 Espaciado, radios, sombras y medidas

| Token | Valor | | Token | Valor |
|---|---|---|---|---|
| `space-1` | 4 px | | `radius-sm` | 4 px (campos, casillas) |
| `space-2` | 8 px | | `radius-md` | 8 px (botones, tarjetas) |
| `space-3` | 12 px | | `radius-lg` | 16 px (formulario, fotos, tarjeta del titular) |
| `space-4` | 16 px (margen lateral móvil) | | `radius-pill` | 999 px (íconos de ejes, chips, redes) |
| `space-5` | 24 px (margen lateral escritorio, gap de grillas) | | `sombra-1` | Tarjetas |
| `space-6` | 32 px | | `sombra-2` | Formulario flotante, menú móvil, banner de cookies |
| `space-7` | 48 px (padding de sección en móvil) | | `ancho-contenido` | 1200 px |
| `space-8` | 64 px (padding de sección en escritorio) | | `ancho-lectura` | 720 px |
| `space-9` | 96 px | | `alto-encabezado` | 72 px (64 en móvil) |
| | | | `toque-minimo` | 48 px |

## 4. Reglas de uso del color

### 4.1 Papel de cada familia

| Familia | Papel | Dónde **sí** | Dónde **no** |
|---|---|---|---|
| Crema y blanco | Superficie | Fondo de toda página; tarjetas y formularios | — |
| Azul | Institución, solidez | Titulares, navegación, franja de redes, pie, botón secundario | Botón principal |
| Coral | Acción y marca | Botón principal (`coral-texto`), íconos de ejes (`coral`), «ME PLANTO», la flor | Texto pequeño en `coral` (usar `coral-texto`); más de dos puntos de atención por pantalla |
| **Verde** | **Escucha, comunidad y bienestar** | Rosa (tallo, hojas, raíces), franja «Raíces», aviso de escucha, llamado al buzón, comunas, chips de tema, fechas de agenda, éxito | Botones principales e íconos de ejes (siguen en coral) |

**Decisión del 8 de octubre de 2026:** el verde es el color que la ciudadanía asocia con el bienestar (29,2 %, Encuesta Itagüí 2026; dato interno, no se publica) y pasó de tres usos fijos a ser el color de todo lo que es **escucha y comunidad**. Registrado en el manual de marca.

### 4.2 Mapa de color de la página de inicio

| Sección (en orden) | Fondo | Acentos |
|---|---|---|
| Encabezado | crema | menú azul, subrayado activo coral, botón «Súmate» coral-texto |
| Entrada (banner + registro) | crema; foto a todo el ancho en escritorio | titular azul, lema azul + coral, rosa, botón coral-texto, formulario blanco con sombra-2 |
| Aviso de escucha | **verde-profundo** | rótulo verde-tenue, texto crema, ícono brote en círculo verde-raiz, botón claro (crema con texto verde-profundo) |
| Ejes | blanco | íconos coral, botón secundario azul |
| Raíces | **verde-tenue** | título verde-profundo, rosa grande si no hay foto |
| Cifras (apagada por defecto) | azul-itagui | valores coral-claro |
| Comunas | crema | tarjetas blancas, número **verde-profundo**, hover verde |
| Buzón | **verde-tenue** | título verde-profundo, botón coral-texto |
| Noticias | crema | chips **verde** |
| Agenda | blanco | fechas **verde-profundo** |
| Redes | azul-itagui | rótulo coral-claro, botones WhatsApp blanco y fantasma |
| Pie | azul-profundo | rótulos coral-claro, enlaces crema |

## 5. Tipografía

- **Montserrat** (400, 500, 700, 800) autoalojada: `@fontsource/montserrat`, solo el subconjunto latino (incluye tildes, ñ y ü). Pesa unos 19 KB por peso. `font-display: swap`.
- **Permanent Marker**: solo el lema. Es sustituto provisional del rotulado a pincel; cuando Comunicaciones entregue el lettering en SVG se reemplaza (§17.6).
- Pila de respaldo: `Montserrat, "Helvetica Neue", Arial, sans-serif`.
- Mayúsculas sostenidas solo por CSS (`ra-etiqueta`), nunca escritas en el texto.
- Sin «¡!» en titulares; tuteo.

## 6. Retícula, espaciado y puntos de quiebre

| Punto | Qué cambia |
|---|---|
| < 381 px | Botón «Súmate» del encabezado con padding reducido |
| ≥ 640 px | Grillas de 2 columnas (`ra-grilla-2`, `ra-grilla-3`) |
| ≥ 768 px | Contenedor con margen de 24 px; secciones con 64 px de padding; titular 56 px; lema 48 px; rosa del lema 176 px; comunas en 4 columnas; pie en 3 columnas |
| ≥ 900 px | Banner y `ra-dos-columnas` en 2 columnas; **entrada con foto a todo el ancho** |
| ≥ 1024 px | Menú horizontal visible; desaparece la hamburguesa; grillas de 3 y 4 columnas |

- Contenedor: `ra-contenedor`, máximo 1200 px y centrado.
- Sección: `ra-seccion` (48/64 px vertical); `ra-seccion-compacta` (24 px).
- Columnas de contenido: `ra-dos-columnas` (1 columna en móvil, 2 desde 900 px, gap 32–48 px).
- Ancho de lectura: `ra-lectura` (720 px); formularios angostos: `ra-angosto` (560 px).

## 7. Iconografía

Componente `<x-ra.icono nombre="…" />` (`resources/views/components/ra/icono.blade.php`). Lucide (MIT), trazo de 2 px, `aria-hidden`.

| Nombre | Uso | | Nombre | Uso |
|---|---|---|---|---|
| `heart-pulse` | Eje salud | | `map-pin` | Lugar de evento |
| `users` | Eje familias | | `calendario` | Agregar a calendario |
| `graduation-cap` | Eje jóvenes | | `whatsapp` | Botones de WhatsApp |
| `store` | Eje comerciantes | | `exito`, `alerta`, `alerta-circulo` | Estados y errores |
| `shield` | Eje seguridad | | `arrow-right` | Botones de avance |
| `trending-up` | Eje oportunidades | | `menu`, `cerrar` | Menú móvil |
| `sprout` | Eje ciudad que avanza; aviso de escucha | | `enlace`, `compartir`, `subir` | Utilidades |
| `facebook`, `instagram`, `tiktok` (relleno), `x`, `youtube` | Redes | | | |

Íconos de ejes: blanco dentro de un círculo coral de 48 px (64 px en la cabecera del eje). Sin emojis como íconos.

## 8. Ilustración «Aquí me planto» (RosaRaices)

Versión 2 (8 oct 2026). Propuesta de Tecnología hasta la pieza definitiva de Comunicaciones.

### 8.1 Archivos

| Archivo | Uso |
|---|---|
| `resources/views/components/ra/rosa-raices.blade.php` | En línea en el sitio, con clases `ra-r-*` (toma los colores de los tokens) |
| `public/img/rosa-raices.svg` | Uso fuera del sitio, con colores fijos y `<title>` |
| `public/img/redes-por-defecto.jpg` | Imagen 1200 × 630 para compartir en redes (lema, marca y rosa) |

### 8.2 Estructura (viewBox `0 0 240 380`, de atrás hacia adelante)

| Capa | Clase | Color | Idea que comunica |
|---|---|---|---|
| Montañas del valle | `ra-r-montana` | azul-claro al 55 % | Itagüí en el valle de Aburrá |
| Raíces (13 trazos, de 9 a 2,5 px) | `ra-r-raiz` | verde-profundo | Se aferra a la tierra: «me planto» |
| Tierra con borde irregular | `ra-r-tierra` | azul-itagui | El suelo de Itagüí |
| Texto ITAGÜÍ | `ra-r-texto` | crema, 15 px, 800, tracking .2em | |
| Tallo recto de 8 px | `ra-r-tallo` | verde-raiz | Firmeza |
| Tres espinas | `ra-r-espina` | verde-raiz | Espinas para defender, no para atacar |
| Hoja izquierda / derecha, hacia arriba | `ra-r-hoja` / `ra-r-hoja-oscura` + `ra-r-vena` | verde-raiz / verde-profundo, venas verde-tenue | Crecimiento |
| Cáliz | `ra-r-hoja` | verde-raiz | |
| Flor: base, pétalos traseros, cáliz interno, botón, pétalos laterales y frontales | `ra-r-petalo-sombra`, `ra-r-petalo-int`, `ra-r-petalo`, `ra-r-brillo` | coral-oscuro, coral-texto, coral, coral-claro | Cuando florece, todo cambia alrededor |

### 8.3 Dónde aparece y a qué tamaño

| Lugar | Tamaño |
|---|---|
| Junto al lema (`<x-ra.lema />`): entrada, Súmate, manifiesto, gracias | 120 px móvil · 176 px escritorio · 112 px en la tarjeta de la entrada con foto |
| Franja «Raíces» de inicio, si no hay foto | máximo 280 px |
| `/enlaces` | 96 px |

Por debajo de unos 90 px de ancho la flor pierde lectura; no usarla más pequeña.

## 9. Componentes Blade

Todos en `resources/views/components/ra/`. Se usan como `<x-ra.nombre>`.

| Componente | Props principales | Clases | Notas |
|---|---|---|---|
| `boton` | `href`, `variante` (principal, secundario, fantasma, whatsapp, claro), `icono`, `iconoInicio`, `bloque`, `type` | `ra-btn ra-btn-{variante}` | Alto mínimo 48 px. Con `href` es `<a>`, sin él `<button>` |
| `marca` | — | `ra-marca` | Marca tipográfica ROSA / ACEVEDO / ALCALDESA DE ITAGÜÍ. No dibujar un logotipo |
| `encabezado` | `menu`, `redes` | `ra-encabezado`, `ra-menu`, `ra-menu-movil` | Fijo arriba; menú máximo 6 ítems; «Súmate» fijo; hamburguesa bajo 1024 px; Escape cierra; incluye «Saltar al contenido» |
| `banner` | `banners`; slot `formulario` | `ra-banner`, `ra-banner-grid` o `ra-entrada` | Ver §9.1 |
| `lema` | — | `ra-lema-fila` | «Aquí me planto.» + rosa |
| `rosa-raices` | `etiqueta` (texto alternativo) | `ra-rosa` | §8 |
| `aviso-escucha` | — (lee el ajuste `aviso_global`) | `ra-escucha` | Franja verde bajo la entrada de inicio y de comunas. Separa el texto por «:» en rótulo y frase |
| `formulario-registro` | `titulo`, `etiqueta`, `boton`, `comunaId`, `eventoId`, `pasos` (2 o 3), `id` | `ra-form`, `ra-pasos` | Ver §12.3. Sin JavaScript envía el paso 1 por HTML clásico |
| `consentimiento` | `tipo`, `nombre`, `obligatorio`, `nota` | `ra-check`, `ra-check-opcional` | Texto exacto de la versión vigente de la política; nunca premarcada; la opcional va en caja azul-tenue |
| `campo` | `nombre`, `etiqueta`, `tipo` (text, tel, email, textarea, select), `ayuda`, `opcional` | `ra-campo`, `ra-input`… | Asocia etiqueta, ayuda y error con `aria-describedby`; error con ícono y texto |
| `antispam` | `comunaId`, `eventoId` | `ra-trampa` | Campo trampa, origen oculto y Turnstile |
| `ejes` | `ejes` | `ra-eje` | «Por la **salud**» con ícono circular coral |
| `buzon` | `temas`, `barrios`, `temaId`, `comunaId` | `ra-form` | Aviso de uso de IA en `ra-aviso-alerta` |
| `selector-comuna` | `comunas`, `actual` | `ra-comunas`, `ra-comuna` | Siete comunas + corregimiento; conteo de barrios solo si hay catálogo cargado |
| `tarjeta-noticia` | `noticia`, `nivel` | `ra-tarjeta` | Toda la tarjeta es clicable (`::after`); foco visible en la tarjeta; chip verde |
| `agenda` | `eventos`, `nivel` | `ra-evento`, `ra-fecha` | Fecha en cuadro verde-profundo |
| `franja-redes` | `redes`, `hashtag` | `ra-inverso` | WhatsApp con código de origen; canal de difusión |
| `redes` | `redes` | `ra-redes`, `ra-red` | Círculos de 48 px |
| `compartir` | `url`, `titulo` | `ra-compartir` | Facebook, WhatsApp, X y copiar enlace, con `utm_source` |
| `pie` | `redes`, `menuSitio`, `menuTransparencia` | `ra-pie` | Responsable del tratamiento, leyenda de financiación (modo campaña), preferencias de cookies |

Layout: `resources/views/components/layouts/sitio.blade.php` (`<x-layouts.sitio>`), con props `titulo`, `descripcion`, `imagenRedes`, `canonica`, `tipoOg`, `datosEstructurados` y `noIndexar`. Carga Vite, Umami y Turnstile si están configurados, metadatos Open Graph y X, y el banner de cookies.

### 9.1 Entrada (banner + registro)

| Situación | Clase | Móvil | Escritorio (≥ 900 px) |
|---|---|---|---|
| Inicio con foto | `ra-entrada` | Titular → foto 4:5 → formulario montado 64 px sobre la foto | Foto 16:9 a todo el ancho (máx. 440 px de alto, `object-position: center 15%`). Debajo, en 7fr / 5fr: tarjeta crema del titular (sube 96 px sobre la foto) y formulario (sube 144 px) |
| Inicio sin foto | `ra-banner-grid` | Titular → formulario | Titular a la izquierda, formulario a la derecha |
| Comuna u otras páginas (sin formulario) | `ra-banner-grid` | Titular → foto | Titular y foto en dos columnas |

Varios banners activos forman un carrusel: los textos y las fotos cambian juntos, el formulario se queda. Se navega con puntos de 24 px.

## 10. Páginas y su composición

| Ruta | Vista | Composición |
|---|---|---|
| `/` | `sitio/inicio` | Bloques de la página «inicio» (§11), en el orden del panel; el aviso de escucha va después del bloque «registro» |
| `/conoce-a-rosa`, `/manifiesto`, `/uso-de-ia` y toda página creada en el panel (`/{ruta}`) | `sitio/pagina` | Cabecera (h1 display; lema en el manifiesto) → bloques → compartir (Conoce y Manifiesto) → llamado «Súmate» |
| `/propuestas` | `sitio/propuestas` | Cabecera → lista de ejes en fondo blanco → llamado al buzón |
| `/propuestas/{eje}` | `sitio/eje` | Cabecera con ícono → bloques del eje → compromisos (tarjetas con check verde) → propuestas destacadas → noticias → buzón filtrado (verde) → compartir → otros ejes |
| `/comunas`, `/comunas/{slug}` | `sitio/comunas`, `sitio/comuna` | Banners de la comuna → saludo + registro con comuna preseleccionada → aviso de escucha → bloques → destacadas → agenda → noticias → barrios → otras comunas |
| `/noticias`, `/noticias/{slug}` | `sitio/noticias`, `sitio/noticia` | Grilla de 3 / artículo con imagen destacada, prosa, video, galería, compartir y relacionadas |
| `/agenda`, `/agenda/{slug}` | `sitio/agenda`, `sitio/evento` | Lista / detalle con formulario «Asistiré» y archivo .ics |
| `/buzon` | `sitio/buzon` | Texto + formulario |
| `/sumate` (+ continuar, gracias) | `sitio/sumate*` | Lema + registro de 3 pasos |
| `/enlaces` | `sitio/enlaces` | Columna centrada de 480 px con botones |
| `/politica-de-datos`, `/mis-datos`, `/transparencia` | `sitio/…` | Legales en `ra-prosa` |
| Errores | `errors/404` (con layout), `errors/500` y `503` (estáticos) | |

## 11. Bloques del constructor de páginas

Definidos en `app/Filament/Bloques.php`; cada uno se pinta con `resources/views/partials/bloques/{tipo}.blade.php`. Todos tienen el campo «Visible» (`activo`).

| Tipo | Campos | Fondo |
|---|---|---|
| `registro` (solo inicio) | etiqueta y título del formulario | Entrada §9.1 |
| `raices` (solo inicio) | imagen, alt, etiqueta, título, texto, enlace | verde-tenue |
| `buzon` (solo inicio) | etiqueta, título, texto, botón | verde-tenue |
| `noticias`, `agenda`, `redes` (solo inicio) | título | crema / blanco / azul |
| `texto` | título, contenido enriquecido | crema |
| `imagen` | imagen, alt (obligatorio), pie | crema |
| `imagen_texto` | imagen, alt, etiqueta, título, texto, invertir, fondo (crema o «Raíces») | crema o verde-tenue |
| `cita` | texto, autor | tarjeta blanca |
| `linea_tiempo` | etiqueta, título, hitos (periodo, cargo, texto) | verde-tenue, línea verde-raiz |
| `video` | URL de YouTube o Vimeo, título | carga al hacer clic |
| `galeria` | imágenes, alt | grilla de 3 |
| `cifras` | hasta 4 (valor, texto) | azul-itagui |
| `llamado` | etiqueta, título, texto, botón | azul-itagui |
| `formulario` | tipo (registro o buzón), título | crema |
| `ejes`, `comunas` | etiqueta, título | blanco / crema |

`[POR CONFIRMAR]` se resalta solo (`App\Support\Texto::marcarPendientes`) con fondo alerta-tenue.

## 12. Interacción (Alpine.js)

Archivos en `resources/js/`: `sitio.js` (arranque), `registro.js`, `origen.js` y `cookies.js`.

| Interacción | Comportamiento |
|---|---|
| Menú móvil | `x-show` con transición de opacidad; Escape o clic afuera lo cierran; `aria-expanded` sincronizado |
| Carrusel | Un estado `actual`; sin reproducción automática |
| Registro | Paso 1 → 2 → 3 → gracias en el mismo lugar; el foco pasa al título de cada paso; errores 422 en cada campo; botón «Enviando…» deshabilitado; «Ahora no» termina. Retoma un registro a medias desde `localStorage` |
| Cookies | Barra fija abajo con sombra-2; aceptar, rechazar y configurar al mismo nivel; reabre desde el pie |
| Video | Portada azul-profundo con botón; el iframe se crea al hacer clic |
| Compartir | Copiar enlace con aviso «Enlace copiado» (`role="status"`) |
| Movimiento | `prefers-reduced-motion` desactiva transiciones y el desplazamiento suave |

## 13. Imágenes y medios

| Pieza | Formato recomendado | Notas |
|---|---|---|
| Banner escritorio | 16:9, ≥ 1600 px de ancho | Rosa en la mitad superior (la parte baja la tapan las tarjetas en inicio) |
| Banner móvil | 4:5, ≥ 800 px | Si falta, se usa la de escritorio |
| Destacada de noticia | 16:9 | Tarjeta 16:9; artículo hasta 1200 px |
| Redes (Open Graph) | 1200 × 630 | Conversión `og` automática; por defecto `img/redes-por-defecto.jpg` |
| Bloques de páginas | WebP o JPG optimizado ≤ 200 KB | Los bloques no generan conversiones |

Conversiones automáticas (medialibrary, en cola): WebP de 400, 800, 1200 y 1600 px y `og` JPG de 1200 × 630. El HTML usa `<picture>` con `srcset`; mientras no existen, muestra el original. La primera imagen de la entrada lleva `fetchpriority="high"` y las demás `loading="lazy"`. Texto alternativo obligatorio en el panel.

## 14. Accesibilidad

Objetivo WCAG 2.1 AA (RNF-03).

- **Foco:** anillo de 3 px `foco` con separación de 2 px; `foco-inverso` sobre fondos azules y verde profundo. Nunca se quita.
- **Controles:** mínimo 48 px de alto; casillas de 22 px dentro de etiquetas clicables.
- **Formularios:** etiqueta visible por campo; ayuda y error enlazados con `aria-describedby`; error con ícono y texto, nunca solo color; avisos de error con `role="alert"` y de éxito con `role="status"`.
- **Navegación:** «Saltar al contenido», `aria-current="page"` en el menú y foco gestionado entre pasos del registro.
- **Imágenes:** `alt` obligatorio; íconos decorativos con `aria-hidden`; la rosa tiene `role="img"` y `aria-label`.
- **Verificación:** axe (wcag2a/aa, 21a/aa) en `/`, `/sumate`, `/buzon`, `/propuestas/salud`, `/mis-datos` y `/conoce-a-rosa`, a 390 y 1280 px; flujo de registro completo solo con teclado.
- **Hallazgo abierto:** el coral `#f45a43` del «me planto.» sobre crema da 2,97:1 (axe lo marca; el mínimo para texto grande es 3:1). Es decisión de marca (§18).

## 15. Panel administrable

`app/Providers/Filament/AdminPanelProvider.php`:

| Elemento | Valor |
|---|---|
| Primario | `#003c57` (azul-itagui) |
| Peligro | `#c23a24` (coral-texto) |
| Éxito / advertencia | `#2f7d5b` / `#9a5b00` |
| Grises | Slate |
| Fuente | Montserrat local (`public/css/montserrat-panel.css`) |
| Avatar | Iniciales en SVG local (`App\Filament\Support\AvatarIniciales`); sin servicios externos |
| Navegación | Grupos Contenido · Ciudadanía · Sitio |

Los recursos de Filament (`public/css/filament`, `public/js/filament`) no están en Git: se generan con `filament:upgrade` en cada `composer install` y con `filament:assets` en el despliegue.

## 16. Rendimiento y caché: efectos sobre los cambios de diseño

- Presupuesto (RNF-02): LCP < 1,5 s en 4G; inicio < 900 KB; CLS < 0,1. Hoy: CSS ≈ 28 KB (5,8 KB gzip), JS ≈ 61 KB (22 KB gzip), fuentes ≈ 105 KB.
- Toda imagen lleva `width` y `height` para no mover la página (CLS).
- En producción las páginas públicas se guardan completas (spatie/responsecache). Guardar contenido en el panel limpia la caché; **un cambio de CSS, JS o vistas requiere despliegue**, y el despliegue limpia la caché.
- Los nombres de archivo de Vite cambian con cada compilación, así que el navegador y Cloudflare no sirven estilos viejos.

## 17. Cómo modificar el diseño (procedimientos)

### 17.1 Cambiar un color o crear un token

1. Editar `docs/tokens.json` (valor y uso, con el contraste calculado).
2. Reflejarlo en `resources/css/tokens.css`.
3. Si es un color de marca usado por el panel, actualizar `AdminPanelProvider` y, si aplica, `public/img/rosa-raices.svg`.
4. Verificar el contraste: 4,5:1 para texto normal, 3:1 para texto ≥ 24 px y para controles.
5. Actualizar §3 y §4 de este documento.

### 17.2 Cambiar el papel de un color en una sección

Agregar o modificar la regla en `sitio.css`, en la sección correspondiente, sin tocar el kit. Actualizar el mapa §4.2.

### 17.3 Crear un componente

1. `resources/views/components/ra/{nombre}.blade.php` con `@props` y un comentario de propósito.
2. Estilos `ra-{nombre}` en `sitio.css`, solo con tokens.
3. Etiquetas y estados accesibles (§14).
4. Agregarlo a la tabla §9.

### 17.4 Crear un bloque para el panel

1. `Block::make('tipo')` en `app/Filament/Bloques.php`, con el campo `activo`.
2. `resources/views/partials/bloques/{tipo}.blade.php` que reciba `$d`.
3. Agregarlo a §11.

### 17.4 bis Crear una página nueva

Desde el panel (Contenido → Páginas): la dirección se llena sola con el título (`/nuestro-equipo`), solo se ve si está «Publicada», y la sección «Menú» la pone en el menú principal (máximo 6 ítems) o en el pie. Las direcciones que ya usa el sitio están reservadas (`App\Models\Pagina::RESERVADAS`); si se crea una sección nueva con ruta propia en el código, agregarla a esa lista.

### 17.5 Cambiar la entrada de inicio

`components/ra/banner.blade.php` (estructura) y la sección «Entrada de inicio con foto» de `sitio.css`. Probar en 390, 768, 1280 y 1440 px, con uno y varios banners, con y sin foto.

### 17.6 Reemplazar la rosa o el lema por la pieza de Comunicaciones

- **Rosa:** reemplazar el contenido del SVG en `rosa-raices.blade.php` manteniendo las clases `ra-r-*` o ajustándolas; reemplazar `public/img/rosa-raices.svg`; regenerar `redes-por-defecto.jpg`.
- **Lema:** cuando llegue el lettering SVG, sustituir el `<p class="ra-lema">` de `lema.blade.php` por el SVG con `role="img"` y `aria-label="Aquí me planto."`; retirar Permanent Marker de `fuentes.css`. Esto también resuelve el hallazgo de contraste si el SVG se diseña con 3:1 o más.

### 17.7 Agregar un ícono

Añadir el trazo Lucide al arreglo `$trazos` (o `$rellenos`) de `icono.blade.php`. Para los ejes, también en las opciones de `EjeResource`.

### 17.8 Verificar antes de entregar

```bash
npm run build
vendor/bin/pint
php artisan test
```

Además: revisión visual en móvil (390 px) y escritorio (1280 y 1440 px), auditoría axe de las páginas de §14 y actualización de este documento en el mismo commit.

## 18. Decisiones registradas y pendientes

| Fecha | Decisión | Estado |
|---|---|---|
| 6 oct 2026 | Kit `ra-` y tokens como base; Montserrat autoalojada | Vigente |
| 8 oct 2026 | Verde como color de escucha y comunidad (§4) | Vigente; validar con Comunicaciones |
| 8 oct 2026 | Aviso de escucha: de encima del menú a franja verde bajo la entrada | Vigente |
| 8 oct 2026 | Rosa «Aquí me planto» versión 2 (§8) | Propuesta hasta la pieza de Comunicaciones |
| 9 oct 2026 | Entrada en escritorio con foto a todo el ancho y tarjetas montadas (§9.1) | Vigente |
| Pendiente | Contraste del coral del lema (2,97:1) | Decide Comunicaciones: oscurecer levemente o esperar el lettering SVG |
| Pendiente | Logotipo y lettering «Aquí me planto» en SVG | Comunicaciones |
| Pendiente | Fotografías definitivas (16:9 y 4:5) | Comunicaciones |
| Pendiente | Actualizar `docs/componentes/*.html` con los cambios de octubre | Tecnología |
