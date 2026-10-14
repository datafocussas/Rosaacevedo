# Diseño visual v2 · «Noche de raíces» · rosaacevedo.com

Especificación para implementar el rediseño visual aprobado el 9 de octubre de 2026. **Solo cambia la capa visual** (color, tipografía, imágenes, distribución y estilo de componentes). No cambian rutas, modelos, migraciones, lógica de formularios, consentimientos, integración con el CRM ni reglas de negocio.

| | |
|---|---|
| Versión | 2.0 · 9 de octubre de 2026 |
| Aprobó | Yeyson Henao, líder de Tecnología |
| Reemplaza | §3 a §8 de `docs/Diseno_Sitio_Web.md` (v1.0). El resto de ese documento sigue vigente |
| Referencia visual | Lienzo «Rosa Alcaldesa 2027 — Rediseño visual» (cuatro láminas) y `maquetas/*.png` de esta entrega |
| Archivos | `tokens-v2.css`, `piezas/`, `maquetas/`, `../favicon/` |

Si este documento y las maquetas difieren, **manda este documento**: las maquetas muestran un caso con contenido de ejemplo; aquí están las reglas para cualquier contenido que se cargue desde el panel.

---

## 1. Principios

1. **Lujo sereno, raíz firme.** El lujo sale de la noche profunda, el oro como hilo fino, la fotografía cinematográfica y el espacio generoso. Nunca de adornos, brillos animados ni letras decorativas.
2. **Regla 70/30.** El claro (marfil, arena, blanco) ocupa al menos el 70 % de cada página. La noche se usa solo donde hay impacto y poco texto: encabezado con foto, cita, redes, pie y cabeceras de páginas internas. Razón: la lectura es mejor con texto oscuro sobre fondo claro para todas las edades, y el negro se asocia con exclusividad, lo que choca con un electorado de estratos 2 y 3 y con el concepto de cercanía.
3. **Legibilidad política.** Una sola familia (Montserrat), titulares en 700–800, sin cursivas ni trazos finos, cuerpo de 17–18 px. Todo texto debe leerse a la primera en un celular de gama media con sol.
4. **El panel manda en el contenido; el diseño se adapta.** Ningún componente puede depender de un texto, una foto o una cantidad exacta. Las reglas de §8 dicen qué pasa con textos largos, fotos de cualquier encuadre, cero, uno o muchos elementos.
5. **Se conserva la esencia:** «Aquí me planto», la rosa, el azul de Itagüí (ahora como noche), el coral de la pieza oficial y el verde del bienestar.

## 2. Qué cambia frente a v1

| Tema | v1 | v2 |
|---|---|---|
| Fondo dominante | Crema `#f8f3ef` | Marfil `#f6f1ea` (70 %) + noche `#04151f` (30 %) |
| Azul de marca | `#003c57` en titulares | Se profundiza a noche `#04151f`; titulares sobre claro en noche |
| Coral | `#f45a43` (acento), `#c23a24` (botón con texto blanco) | `#ff6b4e` vivo con **texto noche** en botones; `#b8321f` para texto coral sobre claro |
| Nuevo | — | Oro `#c9a86a` / `#e2c58b` / `#7d5f27` para filos, rótulos y cifras |
| Verde | Escucha y comunidad en tonos `#1f5a40` / `#e2f0e8` | Se mantiene el papel; esmeralda `#0f5c45` (buzón, números de comuna, fechas) y jade `#3ddc97` sobre oscuro |
| Tipografía | Montserrat + Permanent Marker (lema) | **Solo Montserrat**. Se retira Permanent Marker |
| Lema | Pincel, rotado −3° | Montserrat 800, sin rotar; «Aquí» marfil y «me planto.» coral |
| Encabezado de inicio | Foto en tarjeta + titular en tarjeta crema | Foto cinematográfica a sangre a la derecha, fundida con la noche |
| Rosa | v2 con montañas y tierra | v3 «Raíz de poder»: raíz pivotante, raíces simétricas y roca estratificada |
| Degradados | Prohibidos | Solo los fundidos de la foto con la noche y el halo de la flor. Nunca en botones, textos ni tarjetas |
| Radios | 4 / 8 / 16 px | 14 (campos) / 22 (tarjetas) / 28 (paneles) / píldora (botones) |

## 3. Tokens

`tokens-v2.css` reemplaza `resources/css/tokens.css`. Incluye al final **alias de compatibilidad** (`--crema`, `--azul-itagui`, `--coral-texto`, etc.) que apuntan a los valores nuevos, para que nada se rompa durante la migración. Retirarlos cuando ninguna vista los use. `docs/tokens.json` debe regenerarse a partir de este archivo.

### 3.1 Superficies

| Token | Valor | Uso |
|---|---|---|
| `--noche` | `#04151f` | Encabezado de inicio, cabecera de páginas internas, cita, redes |
| `--abismo` | `#0a2533` | Oscuro alterno (bloque «Raíces», cifras) |
| `--profundo` | `#020d14` | Pie de página |
| `--marfil` | `#f6f1ea` | Fondo claro principal |
| `--arena` | `#ece4d8` | Claro alterno (agenda, tarjeta de El Manzanillo, bloques alternos) |
| `--blanco` | `#ffffff` | Tarjetas y campos sobre marfil |
| `--esmeralda` | `#0f5c45` | Buzón; aviso de escucha; números de comuna y fechas sobre claro |

### 3.2 Texto y acentos

| Token | Valor | Dónde | Contraste |
|---|---|---|---|
| `--tinta` | `#04151f` | Texto y titulares sobre claro | 16,5:1 marfil · 14,7:1 arena |
| `--tinta-suave` | `#4f5f68` | Secundario sobre claro | 5,9:1 marfil · 5,3:1 arena · 6,6:1 blanco |
| `--marfil-texto` | `#f4eee6` | Texto sobre oscuro | 16,1:1 noche · 13,8:1 abismo · 6,9:1 esmeralda |
| `--niebla` | `#9fb3bf` | Secundario sobre oscuro | 8,5:1 noche · 7,3:1 abismo |
| `--coral` | `#ff6b4e` | Botón principal (texto `--noche`, 6,6:1); «me planto.»; flor; texto coral sobre oscuro | 6,6:1 sobre noche |
| `--coral-hondo` | `#b8321f` | Texto coral sobre claro («por…», «VER TODAS») | 5,3:1 marfil · 6,0:1 blanco |
| `--oro` | `#c9a86a` | Rótulos y filos sobre oscuro | 8,2:1 noche |
| `--oro-claro` | `#e2c58b` | Cifras, enlaces, foco sobre oscuro; rótulos sobre esmeralda | 11,1:1 noche · 4,8:1 esmeralda |
| `--oro-oscuro` | `#7d5f27` | Rótulos y numerales sobre claro | 5,3:1 marfil · 5,9:1 blanco |
| `--jade` | `#3ddc97` | Verde sobre oscuro: botón del canal de WhatsApp (texto noche, 10,5:1), rótulo «RAÍCES» | 10,5:1 noche · 9,0:1 abismo |

### 3.3 Bordes, campos y foco

| Token | Valor | Uso |
|---|---|---|
| `--borde-claro` | `#e3d8c8` | Tarjetas sobre marfil (decorativo) |
| `--borde-verde` | `#d3e4da` | Tarjetas de comuna (decorativo) |
| `--separador` | `#d9cdbb` | Filas de la lista de noticias |
| `--filo-oro` | oro al 30 % | Tarjetas y cápsulas sobre oscuro (decorativo) |
| `--borde-control` | `#857b70` | **Borde de campos y casillas sobre claro** (3,7:1) |
| `--campo-oscuro` | marfil al 45 % | **Borde de campos sobre noche** (4,1:1) |
| `--campo-esmeralda` | marfil al 60 % | Borde de campos sobre esmeralda |
| `--foco-claro` / `--foco-oscuro` | noche / oro claro | Anillo de foco de 2 px con 3 px de separación, según el fondo |

### 3.4 Estados

Se conservan de v1: `--exito` `#2f7d5b`, `--alerta` `#9a5b00`, `--alerta-tenue` `#fbf0dc`, `--error` `#a4231b`. Sobre fondo oscuro, los mensajes de error usan `--error-sobre-oscuro` `#ff8a75` (8,1:1). Todo estado lleva ícono y texto, nunca solo color.

## 4. Tipografía

Una familia: **Montserrat** autoalojada (pesos 400, 600, 700 y 800; ya está en `fuentes.css`). **Retirar Permanent Marker** de `fuentes.css` y de `ra-lema`.

| Estilo | Escritorio | Móvil | Peso | Interletrado | Uso |
|---|---|---|---|---|---|
| Lema | 92/87 px | 54/51 px | 800 | −0,02 em | «Aquí me planto.» en el encabezado |
| Titular de banner sin lema | 64/66 px | 40/42 px | 800 | −0,02 em | Cuando el banner tiene «mostrar lema» apagado |
| Título de página (h1 interno) | 60/62 px | 38/40 px | 800 | −0,01 em | Cabecera de páginas internas |
| Título de sección (h2) | 60/61 px | 38/39 px | 800 | −0,01 em | «Aquí me planto por…», «Noticias»… |
| Cita | 50/55 px | 30/35 px | 700 | 0 | Bloque cita y franja «Raíces» |
| Título de tarjeta (h3) | 24–34 px | 19–24 px | 700–800 | 0 | Ejes, noticias, eventos |
| Bajada del banner | 26/34 px | 19/25 px | 700 | 0 | «Raíces que permanecen…» |
| Entradilla | 17–18/29 px | 17/27 px | 400 | 0 | Párrafo bajo títulos |
| Cuerpo | 17/28 px | 16/26 px | 400 | 0 | Texto corrido; prosa de noticias 18/30 |
| Secundario | 14–15/22 px | 14/21 px | 400–500 | 0 | Fechas, ayudas, lugares |
| Rótulo | 12/16 px | **11/14 px mínimo** | 700 | 0,30–0,34 em, mayúsculas por CSS | «POR EL FUTURO DE ITAGÜÍ», categorías |
| Botón | 14–15/18 px | 14/18 px | 800 | 0,06 em, mayúsculas por CSS | Todos los botones |
| Cifra | 44 px | 34 px | 800 | 0 | Franja «Raíces», bloque cifras |
| Numeral decorativo | 96 / 40 px | 48 / 26 px | 400 | 0 | Bento de ejes (`aria-hidden`) |

Implementar los tamaños con `clamp()` entre el valor móvil y el de escritorio (por ejemplo, `font-size: clamp(54px, 6.4vw, 92px)` para el lema). En las maquetas de móvil algunos rótulos están en 10 px: **en producción el mínimo es 11 px**.

## 5. Distribución

- Contenedor: máximo 1240 px, margen lateral de 32 px en escritorio y 20 px en móvil.
- Padding vertical de sección: 104–120 px en escritorio y 48–56 px en móvil.
- Puntos de quiebre: 640, 768, 980 (las rejillas de 2 columnas pasan a 1 y el menú a hamburguesa) y 1240 px.
- **Alternancia de fondos**: dos secciones oscuras nunca quedan juntas. Dos claras seguidas se separan alternando marfil, arena y blanco.

### 5.1 Mapa de la página de inicio

| Orden | Sección | Fondo | Notas |
|---|---|---|---|
| 1 | Encabezado fijo | Vidrio sobre noche (`--vidrio-barra`, desenfoque 14 px, filo oro abajo) | Marca, menú, botón «SÚMATE» coral |
| 2 | Entrada (banner + registro + rosa) | Noche | §6.2 |
| 3 | Aviso de escucha (si está activo) | Esmeralda, franja compacta | §6.11 |
| 4 | Propuestas (bento de ejes) | Marfil | §6.4 |
| 5 | Raíces (cita + cifras) | Abismo | §6.5 |
| 6 | Tu comuna | Marfil | §6.6 |
| 7 | Buzón | Esmeralda | §6.7 |
| 8 | Noticias | Marfil o blanco (alterna con 6) | §6.8 |
| 9 | Agenda | Arena | §6.9 |
| 10 | Redes | Noche | §6.10 |
| 11 | Pie | Profundo | §6.12 |

El orden real lo define el panel (bloques de la página «inicio»). La regla de alternancia de §5 se aplica sobre el orden que resulte (§8.3).

### 5.2 Páginas internas

Todas las páginas que no son el inicio (Conoce a Rosa, propuestas, ejes, comunas, noticias, agenda, buzón, súmate, páginas creadas en el panel, políticas) siguen esta estructura:

1. **Cabecera de página** en noche, corta (padding 88 px arriba y 56 px abajo en escritorio; 48/32 en móvil): rótulo oro, h1 marfil (§4), entradilla niebla opcional y, si la página tiene banner o imagen destacada, la foto con el tratamiento cinematográfico a la derecha y 420 px de alto (en móvil, debajo, 4:3).
2. **Contenido en claro**: marfil por defecto, con los bloques del constructor según §7.
3. Pie.

El texto largo (noticias, políticas, páginas) va **siempre** en marfil, a 720 px de ancho de lectura.

## 6. Componentes

Los nombres corresponden a los componentes Blade existentes (`resources/views/components/ra/`). Se cambian estilos y estructura visual; las props y la lógica se conservan salvo donde se indica.

### 6.1 Botones (`boton`)

| Variante | Sobre claro | Sobre oscuro o esmeralda |
|---|---|---|
| `principal` | Fondo coral, texto noche, píldora, 52 px de alto | Igual |
| `secundario` | Fondo noche, texto marfil | Contorno oro claro de 1 px, texto marfil |
| `fantasma` | Contorno noche de 1 px, texto noche | Contorno oro al 45 %, texto marfil |
| `whatsapp` | Fondo blanco, borde `--borde-control`, ícono esmeralda, texto noche | Fondo jade, texto noche (canal) o contorno oro (escríbenos) |
| `claro` | — | Fondo marfil, texto noche |

Texto en mayúsculas por CSS, 800, interletrado 0,06 em. Alto mínimo 48 px (52 recomendado). Padding horizontal de 24–28 px. Estado presionado: −6 % de luminosidad; deshabilitado: opacidad 0,5 y texto «Enviando…».

### 6.2 Entrada de inicio (`banner` + `lema` + `formulario-registro` + `rosa-raices`)

**Escritorio (≥ 980 px)**, rejilla de dos columnas (1,05 fr / 1 fr, separación de 48 px), alineada abajo:

- **Columna izquierda**, en este orden: rótulo (`banners.etiqueta`) · lema si `mostrar_lema` está activo, o el titular en tamaño de titular de banner · bajada (`banners.titular` cuando hay lema) · texto (`banners.texto`) · cápsula de registro · botones del banner.
- **Columna derecha**: la foto del banner a sangre. Ocupa desde 72 px por encima del borde superior de la sección hasta el borde inferior, y desde el inicio de la columna hasta el **borde derecho de la ventana**. En CSS: `right: calc(-1 * max(32px, (100vw - 1176px) / 2))`. `object-fit: cover`.
- **Fundidos obligatorios sobre la foto** (garantizan contraste con cualquier foto que se suba):
  - izquierda: `linear-gradient(to right, var(--noche) 0%, rgba(4,21,31,.55) 28%, transparent 58%)`;
  - abajo: `linear-gradient(to top, var(--noche) 0%, transparent 100%)` en el 38 % inferior;
  - arriba: `linear-gradient(to bottom, var(--noche) 0%, transparent 100%)` en el 22 % superior.
- El **fondo de la sección es noche sólido**: un halo o degradado detrás produce una línea de corte en el borde de la foto.
- **Rosa**: SVG en línea de 240 px de ancho, anclada abajo, a 64 px a la izquierda del inicio de la columna derecha, por delante de la foto. Va siempre, haya o no foto.
- **Nombre** «ROSA MARÍA ACEVEDO JARAMILLO» en rótulo oro claro, abajo a la derecha (32 px de margen inferior y 40 px de margen derecho). Es fijo en la plantilla, no viene del banner.
- **Cápsula de registro**: vidrio (`--vidrio`, desenfoque 10 px), filo oro al 28 %, radio 22 px, padding 22 px, ancho máximo de 560 px. Campos en fila (nombre, celular y botón «ME PLANTO»), que se apilan si no caben. Casilla de autorización debajo. Pasos 2 y 3 en la misma cápsula.

**Móvil (< 980 px)**: rótulo → lema o titular → bajada → bloque de 430 px con la foto a la derecha (290 × 400 px, sale por el borde derecho, fundidos izquierdo, inferior y superior) y la rosa a la izquierda (200 px) → cápsula de registro a todo el ancho, con etiquetas visibles encima de cada campo → botones del banner.

**Carrusel** (varios banners activos): cambian la foto, el rótulo, el lema o titular, la bajada, el texto y los botones. La rosa y la cápsula de registro se quedan. Los puntos van bajo la cápsula en escritorio y bajo la foto en móvil: círculos de 10 px con borde oro claro y relleno al estar activos, con área táctil de 44 px. Transición de 400 ms por opacidad. Sin avance automático.

**Banner sin foto**: la columna derecha muestra solo la rosa, más grande (380 px), centrada y anclada abajo, con su halo.

**Banners de página de comuna** y cabeceras internas: el mismo tratamiento de foto, a 420 px de alto, sin cápsula de registro (el formulario de la comuna va en la sección siguiente, en claro).

### 6.3 Lema (`lema`)

Texto HTML, no imagen: «Aquí» en marfil y salto de línea; «me planto.» en coral. Sin rotación. Lleva la rosa al lado solo cuando aparece fuera de la entrada (Súmate, manifiesto, gracias): rosa de 120 px en móvil y 176 px en escritorio, alineada abajo. Sobre claro, «Aquí» va en noche y «me planto.» en coral hondo.

### 6.4 Ejes (`ejes`): rejilla bento

- Rejilla de 4 columnas en escritorio y de 2 en móvil y tableta; separación de 16 px (10 px en móvil); `grid-auto-flow: dense`.
- **El eje con `orden = 1`** (hoy Salud) es la tarjeta grande: ocupa 2 × 2 en escritorio y el ancho completo en móvil. Fondo noche, radio 28 px, padding 40 px, alto mínimo de 380 px. Lleva numeral «01» en oro de 96 px, rótulo coral («POR LA»), título del eje en 56 px / 800 en marfil y frase en niebla.
- **Demás ejes**: fondo blanco, borde claro, radio 28 px, padding 28 px, alto mínimo de 182 px. Numeral en oro oscuro de 40 px, rótulo en coral hondo (`articulo` en mayúsculas) y título en 28 px / 700 en noche. Sin frase.
- **Última celda**: tarjeta coral con «TODAS LAS PROPUESTAS» y «Conócelas y propón la tuya →» (texto noche), que enlaza a `/propuestas`.
- **Cantidad variable**: con N ejes publicados, la rejilla usa N + 1 celdas (la grande cuenta 4 en escritorio). Si la última fila queda incompleta, la tarjeta coral se estira para llenarla (`grid-column: span X`, calculado en la vista). Con un solo eje se muestran la grande y la coral. Con cero, la sección no se pinta.
- **Numerales**: salen del orden de publicación (01, 02…), son `aria-hidden` y no se guardan en la base de datos.
- **Títulos largos**: se parten en dos líneas como máximo, con `overflow-wrap: anywhere`. Límite recomendado en el panel: 22 caracteres para el sujeto.

### 6.5 Raíces (bloque `raices` y franja de cifras)

Fondo abismo. Dos columnas: a la izquierda, rótulo jade «RAÍCES», cita (el título del bloque, entre comillas tipográficas) en 50 px / 700 marfil, texto en niebla y enlace en oro claro con subrayado de 1 px; a la derecha, hasta 3 cifras en tarjetas de filo oro (valor en 44 px / 800 oro claro y texto en niebla). Si el bloque trae imagen, reemplaza a las cifras y va con el tratamiento cinematográfico (fundido a la izquierda, 420 px de alto). Las cifras solo se publican si están confirmadas con fuente (regla del `CLAUDE.md`).

### 6.6 Tu comuna (`selector-comuna`)

Fondo marfil. Encabezado centrado (rótulo oro oscuro, h2 noche y bajada en tinta suave). Debajo, una **red de raíces** en SVG decorativo (`aria-hidden`): ocho curvas que bajan desde un punto oro central hasta cada columna, con trazo de 2 px y degradado de esmeralda a oro. En móvil la red se oculta. Rejilla de 4 × 2 en escritorio y de 2 × 4 en móvil. Cada tarjeta es blanca, con borde verde, radio 22 px y padding 22 px; número 01–07 en esmeralda de 36 px / 800, nombre en noche 700 y «Comuna N» en tinta suave. El Manzanillo va en arena, con «El M.» en oro oscuro. Al pasar el cursor o con foco: borde esmeralda de 2 px.

### 6.7 Buzón (`buzon`)

Fondo esmeralda. Dos columnas: a la izquierda, rótulo oro claro, h2 marfil («¿Qué necesita **tu barrio?**») y texto; a la derecha, el formulario en una cápsula (noche al 35 %, filo oro al 40 %, radio 28 px). Los temas se muestran como **chips de selección** (botones tipo radio con `aria-pressed`): el activo en fondo oro claro con texto noche, los inactivos con contorno marfil al 60 %. Campos con borde `--campo-esmeralda`. Botón principal coral. El aviso de uso de IA va dentro de la cápsula, en marfil, con ícono. **En la página `/buzon`**, el formulario completo va en claro (marfil, campos blancos con `--borde-control`) y solo la cabecera es oscura.

### 6.8 Noticias (`tarjeta-noticia` y bloque `noticias`)

Disposición editorial: la noticia más reciente es **destacada** (imagen 16:10 con radio 28 px, categoría en rótulo esmeralda + fecha, título en 34 px / 800). A su derecha, una **lista** con las siguientes, separadas por filas (categoría, fecha y título en 24 px / 700, sin imagen).

| Noticias publicadas | Diseño |
|---|---|
| 0 | La sección no se pinta |
| 1 | Solo la destacada, a ancho completo y con la imagen a la izquierda en escritorio |
| 2 o 3 | Destacada + lista de 1 o 2 |
| 4 o más | Destacada + lista de 3 (el bloque muestra como máximo 4) |

Sin imagen destacada, la tarjeta usa un fondo abismo con la rosa sola centrada (no se deja un hueco). En `/noticias`, la primera es destacada y las demás van en una rejilla de 3 columnas con imagen 16:10 y título en 22 px / 700. En el detalle de la noticia: cabecera de página oscura con la imagen cinematográfica y prosa en marfil a 720 px.

### 6.9 Agenda (`agenda`)

Fondo arena. Tarjetas blancas de radio 28 px en rejilla de 3 columnas (1 en móvil). Fecha grande: día en 64 px / 800 esmeralda y mes en rótulo oro oscuro. Título en 19 px / 700, lugar en tinta suave y botón fantasma «ASISTIRÉ». Con 0 eventos futuros, la sección no se pinta en el inicio; en `/agenda` se muestra el texto «No hay encuentros programados. Síguenos para enterarte» con los botones de redes. Eventos cancelados: rótulo «CANCELADO» en error y título tachado.

### 6.10 Redes (`franja-redes`, `redes`)

Fondo noche, centrado. Rótulo oro, hashtag vigente (ajuste `hashtag`) en 80 px / 800 marfil, con la segunda palabra en coral. La vista parte el hashtag en la última mayúscula; si no hay, va todo en marfil. Botones píldora con contorno oro al 45 % por cada red activa, en el orden del panel. El canal de WhatsApp va en jade con texto noche. Hashtags de más de 16 caracteres bajan a 56 px.

### 6.11 Aviso de escucha (`aviso-escucha`)

Franja esmeralda compacta (padding de 20 px) justo debajo de la entrada. Rótulo oro claro, frase en marfil y botón contorno marfil. Se separa por «:» como en v1. Si el aviso está inactivo, no se pinta.

### 6.12 Pie (`pie`)

Fondo profundo. Tres columnas (marca y frase; El sitio; Transparencia) con rótulos oro y enlaces en oro claro. Debajo, la palabra **«Itagüí»** en 260 px / 800 con trazo oro al 45 % y relleno transparente (`-webkit-text-stroke: 1px`; respaldo para navegadores sin soporte: color oro al 12 %), cortada por el borde inferior y `aria-hidden`. En móvil va en 120 px. La línea legal (responsable del tratamiento y financiación en modo campaña) se mantiene en niebla, en 13 px.

### 6.13 Formularios (`campo`, `consentimiento`, `formulario-registro`, `antispam`)

| | Sobre claro | Sobre noche | Sobre esmeralda |
|---|---|---|---|
| Campo | Fondo blanco, borde `--borde-control`, radio 14 px, 52 px de alto | Fondo noche al 60 %, borde `--campo-oscuro`, texto marfil | Fondo noche al 45 %, borde `--campo-esmeralda` |
| Etiqueta | Noche, 13–14 px / 600, siempre visible | Marfil | Marfil |
| Ayuda | Tinta suave | Niebla | Marfil al 85 % |
| Error | `--error`, con ícono | `--error-sobre-oscuro`, con ícono | `--error-sobre-oscuro` |
| Casilla | 20 px, `accent-color` noche | `accent-color` oro | `accent-color` oro |
| Foco | Anillo noche | Anillo oro claro | Anillo oro claro |

En la cápsula del encabezado de escritorio las etiquetas pueden ocultarse visualmente (clase `ra-sr`), pero deben existir en el HTML. En móvil son visibles. La casilla opcional de WhatsApp conserva su caja: azul tenue sobre claro y vidrio sobre oscuro.

### 6.14 Encabezado fijo y menú móvil (`encabezado`)

Barra de 76 px (64 en móvil) en vidrio sobre noche con filo oro abajo. Marca tipográfica: «ROSA» en coral y «ACEVEDO» en marfil (800), con «ALCALDESA · ITAGÜÍ 2027» debajo en rótulo oro (sin «· 2027» en pantallas menores de 360 px). Enlaces del menú en marfil 600; el activo, subrayado en oro de 2 px. Botón «SÚMATE» principal. El menú móvil es un panel noche a pantalla completa con enlaces de 24 px / 700, separadores de filo oro y redes abajo. En páginas cuya cabecera es oscura la barra se mantiene igual; al hacer scroll sobre secciones claras, sigue siendo vidrio noche.

## 7. Bloques del constructor de páginas

Para respetar la regla 70/30 y la alternancia, cada bloque tiene **fondos permitidos**. El campo «Fondo» de `app/Filament/Bloques.php` se reemplaza por estas opciones, y el valor por defecto va en **negrita**.

| Bloque | Fondos permitidos | Diseño v2 |
|---|---|---|
| `registro` (solo inicio) | **noche** (fijo) | Entrada §6.2 |
| `raices` (solo inicio) | **abismo** (fijo) | §6.5 |
| `buzon` (solo inicio) | **esmeralda** (fijo) | §6.7 |
| `noticias` | **marfil**, blanco | §6.8 |
| `agenda` | **arena**, marfil | §6.9 |
| `redes` | **noche** (fijo) | §6.10 |
| `texto` | **marfil**, blanco, arena | Prosa a 720 px; título h2 |
| `imagen` | **marfil**, blanco | Imagen a 1240 px con radio 28 px; pie de foto en tinta suave 14 px |
| `imagen_texto` | **marfil**, arena, abismo | Dos columnas, imagen con radio 28 px; si el fondo es abismo, textos en marfil y niebla. Opción «invertir» se conserva |
| `cita` | **abismo**, marfil | Abismo: cita en 50 px / 700 marfil y autor en rótulo oro. Marfil: cita en noche y comillas en coral hondo |
| `linea_tiempo` | **marfil** | Línea vertical oro de 1 px, puntos esmeralda de 12 px, periodo en rótulo oro oscuro, cargo en 22 px / 700. Los datos biográficos solo se publican si están confirmados |
| `video` | **noche** | Portada con la imagen del video fundida, botón de reproducción circular coral de 72 px con triángulo noche, carga al hacer clic |
| `galeria` | **marfil**, arena | Rejilla de 3 (2 en móvil), radio 22 px; la primera imagen ocupa 2 × 2 si hay 5 o más |
| `cifras` | **abismo** | Tarjetas de filo oro (§6.5), de 1 a 4 |
| `llamado` | **esmeralda**, noche, coral | Rótulo, h2 y texto con botón. En el fondo coral, el texto va en noche y el botón en noche con texto marfil |
| `formulario` | **marfil** | Formulario en versión clara (§6.13) dentro de una tarjeta blanca con radio 28 px |
| `ejes` | **marfil** | Bento §6.4 |
| `comunas` | **marfil**, blanco | §6.6 |

**Fondos oscuros** son noche, abismo, esmeralda y coral; **claros**, marfil, blanco y arena.

## 8. Contenido parametrizable: reglas para que el diseño no se rompa

### 8.1 Banners (módulo Banners)

| Campo | Regla de diseño |
|---|---|
| Imagen de escritorio | 16:9, mínimo 1600 × 900 (se recomienda 2400 × 1350). **Rosa o el sujeto en los dos tercios derechos**, rostro en la mitad superior; el tercio izquierdo queda bajo el fundido. Sin textos ni logos dentro de la imagen |
| Imagen móvil | 4:5, mínimo 800 × 1000, sujeto centrado o a la derecha. Si falta, se usa la de escritorio con `object-position` del punto focal |
| Punto focal | Agregar al formulario del banner un campo «punto focal» (x %, y %; por defecto 62 % / 18 %) que alimenta `object-position`. Así cualquier foto encuadra bien sin recortarla a mano |
| Texto alternativo | Obligatorio (ya lo es) |
| Etiqueta | Máx. 40 caracteres; rótulo en mayúsculas por CSS |
| Mostrar lema | Activo: lema + bajada (el titular pasa a ser la bajada, máx. 70 caracteres). Inactivo: el titular se pinta como titular de banner (64 px), máx. 70 caracteres, en 3 líneas como máximo |
| Texto | Máx. 160 caracteres, en niebla |
| Botones | Hasta 2: el primero `principal`, el segundo `fantasma` sobre oscuro. Máx. 22 caracteres cada uno |
| Página | Inicio (§6.2) o comuna (cabecera de 420 px, §6.2) |
| Variante A/B | No afecta el diseño |
| Sin banners activos | La entrada se pinta con el lema por defecto, la bajada «Raíces que permanecen. Compromisos que cumplimos.», la rosa grande sin foto y la cápsula de registro |

Contraste garantizado: los fundidos de §6.2 dejan el texto siempre sobre noche sólida. **Nunca se pone texto sobre la parte visible de la foto.**

### 8.2 Noticias, páginas y eventos

- **Títulos**: noticias máx. 90 caracteres (ya validado), que se ajustan con `text-wrap: balance`. En tarjetas de lista, `line-clamp` a 3 líneas.
- **Imagen destacada**: 16:10 en la tarjeta destacada y 16:9 en el detalle. El punto focal de medialibrary se aplica igual que en los banners. Sin imagen: placeholder abismo con la rosa (§6.8).
- **Cuerpo enriquecido (`ra-prosa`)** sobre marfil: h2 en 32 px / 800 noche, h3 en 24 px / 700, enlaces en coral hondo con subrayado, citas con borde superior oro de 1 px (no lateral) y texto en 22 px / 700, listas con viñeta esmeralda, imágenes con radio 22 px y tablas con filas separadas por `--separador`.
- **Video embebido**: bloque noche §7.
- **Categorías**: rótulo esmeralda sobre claro y jade sobre oscuro.
- **Eventos**: §6.9. La imagen del evento, si existe, va en la cabecera de su página.

### 8.3 Orden de bloques y alternancia automática

Como el panel permite reordenar los bloques, la vista aplica la alternancia por sí sola:

1. Si dos bloques **oscuros** quedan seguidos, el segundo se pinta con su fondo claro permitido (si no tiene ninguno, se inserta un separador de 48 px en marfil).
2. Si dos bloques **claros** con el mismo fondo quedan seguidos, el segundo alterna entre blanco y arena según lo que permita.
3. Al guardar, el panel muestra un **aviso** (no un error) cuando más del 30 % de los bloques visibles de una página son oscuros.

Implementar en un servicio `App\Support\Fondos::resolver(array $bloques): array`, con pruebas unitarias para estas tres reglas.

### 8.4 Ejes, comunas, menú, redes y ajustes

- **Ejes**: bento §6.4 con cualquier cantidad. El ícono Lucide de cada eje ya no aparece en el bento del inicio; sí en la cabecera de la página del eje (círculo coral de 64 px con ícono noche).
- **Comunas**: catálogo fijo de 8. Si una comuna no tiene página publicada, su tarjeta se muestra sin enlace, en tinta suave, con la etiqueta «Pronto».
- **Menú**: hasta 6 ítems (ya validado). Con más de 5 en pantallas de 980–1100 px, el espacio entre ítems baja de 28 a 18 px.
- **Redes**: cualquier cantidad, en el orden del panel; el ícono sale del campo `icono`.
- **Aviso global, modo de sitio, hashtag y WhatsApp**: ver §6.10 y §6.11. En **modo campaña**, el botón principal del encabezado puede cambiar de texto («VOTA», por ejemplo), pero conserva su estilo.

## 9. La rosa v3 «Raíz de poder»

### 9.1 Anatomía (viewBox `0 0 400 760`, de atrás hacia adelante)

| Capa | Color | Significado |
|---|---|---|
| Halo radial | Coral al 35 % → transparente | La flor ilumina |
| Roca en 3 estratos trapezoidales | `#14445a` → `#082233`, `#0b2b3b`, `#072130`, con filos oro al 35 % | La tierra de Itagüí, sólida |
| Línea de horizonte | Oro, 1,5 px | El nivel del suelo |
| «ITAGÜÍ» grabado | Oro claro, Montserrat 700, 19 px, interletrado 10 | El territorio |
| Raíces laterales: 3 pares simétricos y 4 raicillas | Degradado esmeralda → oro → oro claro, 7 / 5 / 4 / 2,5 px | Estabilidad: se abren como un ancla |
| Raíz pivotante | Mismo degradado, forma afilada de 16 a 0 px | Firmeza: atraviesa toda la roca |
| 8 puntos de oro en las puntas | Oro claro, con anillo al 40 % | Los 7 territorios urbanos y El Manzanillo |
| Tallo recto de 12 px y 3 espinas | Degradado esmeralda | Firmeza; espinas para defender |
| 2 hojas con nervadura oro | Esmeralda | Crecimiento |
| Flor: base sólida, pétalo trasero, cáliz, pétalos laterales, copa, espiral y pétalo frontal | Coral `#ff9a7f` → `#f4583f` → `#a82a18`, base `#9e2615`, espiral `#6e170c` | Cuando florece, todo cambia alrededor |
| Filos de pétalos | Oro claro, 1,2 px | El toque de lujo |

### 9.2 Archivos y usos

| Archivo | Uso |
|---|---|
| `piezas/rosa-raices-v3.svg` | Fuente para el componente `rosa-raices.blade.php`: copiar el contenido y cambiar los `id` de los degradados por un prefijo único por instancia (por ejemplo, `rosa-{{ $uid }}-pet`), porque la rosa puede aparecer dos veces en la misma página |
| `piezas/rosa-raices-v3-sin-halo.svg` | Sobre fondos claros o fotos |
| `piezas/rosa-raices-v3-1600.png`, `-sin-halo-1600.png` | Usos fuera del sitio (redes, presentaciones, impresos digitales) |
| `piezas/rosa-flor.svg`, `rosa-flor-1024.png` | Flor sola: tamaños menores de 96 px, marca del panel y avatares |
| `../favicon/` | Favicon e íconos de app (ver su `LEEME-favicon.md`) |
| `piezas/og-redes-1200x630.png` | Reemplaza `public/img/redes-por-defecto.jpg` (convertir a JPG al 85 %) |

### 9.3 Reglas

- Ancho mínimo de la rosa completa: 96 px; por debajo, solo la flor.
- No rotar, no cambiar colores, no separar la flor de las raíces salvo en la versión «flor sola», no poner texto encima.
- Siempre `role="img"` con `aria-label` («Rosa coral con raíces profundas que se anclan en la roca de Itagüí»); cuando acompaña al lema, puede ser decorativa (`aria-hidden="true"`).
- Es una propuesta de Tecnología: si Comunicaciones entrega una ilustración propia, se reemplaza con el mismo procedimiento de §17.6 de v1.

## 10. Fotografía

- **Tratamiento cinematográfico** (encabezados): foto a sangre, sin marco ni radio, fundida con la noche (§6.2). Sin filtros de color; si una foto queda demasiado clara junto a la noche, bajar su exposición en edición, no con CSS.
- **Dentro de secciones claras**: radio de 28 px, sin sombra.
- **Retrato de Rosa**: luz de día, ciudad o montaña al fondo, gesto cercano. La foto actual es un recorte de la pieza oficial, solo para maquetas (`piezas/foto-referencia-rosa-encabezado.jpg`). Se necesitan fotos definitivas en 16:9 y 4:5 (pendiente de Comunicaciones).
- **Peso**: WebP con las conversiones automáticas de medialibrary; la foto del encabezado es el elemento LCP y lleva `fetchpriority="high"`.

## 11. Movimiento

Mínimo y sereno: aparición por opacidad y 12 px de desplazamiento al entrar en pantalla (400 ms, `ease-out`), solo en títulos de sección y tarjetas; el carrusel con transición por opacidad de 400 ms; las raíces de la rosa pueden dibujarse una sola vez al cargar (animar `stroke-dashoffset`, 1,2 s). Todo se desactiva con `prefers-reduced-motion: reduce`. Nada parpadea ni se mueve en bucle.

## 12. Accesibilidad (WCAG 2.1 AA)

- Todos los pares de texto de §3 están verificados. Sobre la foto nunca va texto (§8.1).
- Anillo de foco: 2 px sólido con 3 px de separación, en `--foco-claro` sobre fondos claros y `--foco-oscuro` sobre oscuros y esmeralda.
- Controles de 48 px de alto mínimo; chips de 44 px.
- Bordes de campos: 3:1 o más en cada fondo (§3.3).
- Numerales, red de raíces, «Itagüí» del pie y halo: decorativos (`aria-hidden`).
- Pruebas: axe en `/`, `/sumate`, `/buzon`, `/propuestas/salud`, `/comunas/comuna-4-santa-maria`, `/noticias/{una}`, `/mis-datos` y `/conoce-a-rosa`, a 390, 1280 y 1440 px. Además, el registro completo solo con teclado, sobre fondo oscuro (inicio) y claro (Súmate).
- Queda resuelto el hallazgo abierto de v1 (coral del lema a 2,97:1): ahora es `#ff6b4e` sobre noche, con 6,6:1.

## 13. Panel de administración (Filament)

`AdminPanelProvider`: primario `#04151f`, peligro `#b8321f`, éxito `#2f7d5b`, advertencia `#9a5b00`, grises Slate. Marca: `rosa-flor.svg` (32 px) con «Rosa Acevedo» en Montserrat 800. Favicon: `favicon.svg`. Fondo de la pantalla de ingreso: noche, con la rosa v3 sin halo a la izquierda. El panel conserva su fondo claro de trabajo.

## 14. Plan de implementación sugerido

| Paso | Archivos | Verificación |
|---|---|---|
| 1. Tokens | `resources/css/tokens.css` ← `tokens-v2.css`; regenerar `docs/tokens.json` | `npm run build`; el sitio sigue funcionando gracias a los alias |
| 2. Tipografía | `fuentes.css` (retirar Permanent Marker); clases de tipo en `sitio.css` | Ninguna vista usa `--font-lema` |
| 3. Botones, campos y foco | `sitio.css` §Formularios y §Redes y botones; `boton`, `campo`, `consentimiento` | axe sin errores de contraste |
| 4. Encabezado y entrada | `encabezado`, `banner`, `lema`, `formulario-registro`, `rosa-raices` (v3); campo «punto focal» en Banners | 1, 2 y 3 banners, sin banners, sin foto, título de 70 caracteres; 390, 768, 1280 y 1440 px |
| 5. Secciones de inicio | `ejes`, `selector-comuna`, `buzon`, `tarjeta-noticia`, `agenda`, `franja-redes`, `aviso-escucha`, `pie`; parciales de bloques | Ejes con 1, 4, 7 y 9 elementos; noticias 0–5; agenda 0–3 |
| 6. Páginas internas | Cabecera de página oscura; `ra-prosa` | Noticia larga con imágenes, citas y tablas |
| 7. Bloques y fondos | `app/Filament/Bloques.php` (opciones de fondo), `App\Support\Fondos` y pruebas | Pruebas unitarias de §8.3 |
| 8. Panel, favicon e imagen para redes | `AdminPanelProvider`, `public/` | Vista previa de enlace en WhatsApp y Facebook |
| 9. Documentación | Actualizar `docs/Diseno_Sitio_Web.md` (§3–§8 apuntan a este documento) y `docs/componentes/` | Revisión de Yeyson |

Comandos de cierre (ya establecidos en v1): `npm run build`, `vendor/bin/pint`, `php artisan test`, auditoría axe y revisión visual a 390, 1280 y 1440 px.

## 15. Pendientes de la campaña que afectan el diseño

- Fotografías definitivas de Rosa en 16:9 y 4:5 con espacio a la izquierda para el fundido.
- Logotipo, si Comunicaciones lo tiene. Hoy la marca es tipográfica.
- Validación de la rosa v3 por Comunicaciones.
- Cifras de la franja «Raíces» confirmadas con fuente.
