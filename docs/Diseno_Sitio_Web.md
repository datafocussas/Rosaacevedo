# Diseño del sitio web · rosaacevedo.com

Documento técnico del diseño **tal como está implementado**. Sirve como insumo antes de cualquier modificación visual: dice qué existe, dónde vive, por qué es así y cómo cambiarlo sin romper la marca, la accesibilidad, el rendimiento ni las funciones del panel.

| | |
|---|---|
| Versión | 2.0 · 9 de octubre de 2026 · diseño v2 «Noche de raíces» |
| Responsable | Tecnología (DataFocus SAS) |
| Alcance | Sitio público (Blade + Alpine.js) y tema del panel `/admin` (Filament 3) |
| Especificación visual | `docs/diseno-v2/Diseno_Visual_v2_Noche_de_Raices.md` (las referencias «§x.y» de este documento y del CSS apuntan a ella) |
| Se actualiza | En el mismo commit que cambie el diseño (ver §15) |

## Índice

1. [Jerarquía de fuentes de verdad](#1-jerarquía-de-fuentes-de-verdad)
2. [Qué cambió con la v2 y qué no](#2-qué-cambió-con-la-v2-y-qué-no)
3. [Arquitectura de estilos](#3-arquitectura-de-estilos)
4. [Tokens](#4-tokens)
5. [Fondos, contexto oscuro y regla 70/30](#5-fondos-contexto-oscuro-y-regla-7030)
6. [Tipografía y composición](#6-tipografía-y-composición)
7. [La rosa v3 y la marca tipográfica](#7-la-rosa-v3-y-la-marca-tipográfica)
8. [Componentes Blade](#8-componentes-blade)
9. [Páginas](#9-páginas)
10. [Bloques del constructor y alternancia automática](#10-bloques-del-constructor-y-alternancia-automática)
11. [Imágenes, punto focal y favicon](#11-imágenes-punto-focal-y-favicon)
12. [Movimiento](#12-movimiento)
13. [Accesibilidad](#13-accesibilidad)
14. [Panel administrable](#14-panel-administrable)
15. [Cómo modificar el diseño (procedimientos)](#15-cómo-modificar-el-diseño-procedimientos)
16. [Decisiones registradas y pendientes](#16-decisiones-registradas-y-pendientes)

---

## 1. Jerarquía de fuentes de verdad

| Orden | Fuente | Qué manda |
|---|---|---|
| 1 | `docs/Especificacion_Sitio_Web_Rosa_Alcaldesa_2027.md` | Requerimientos, reglas legales y de contenido (no inventar datos, consentimientos, precampaña) |
| 2 | `docs/diseno-v2/Diseno_Visual_v2_Noche_de_Raices.md` + `docs/diseno-v2/tokens-v2.css` | Lenguaje visual v2: superficies, tipografía, componentes, rosa v3, reglas para contenido parametrizable |
| 3 | `resources/css/tokens.css` (copia de `tokens-v2.css`) y `docs/tokens.json` (generado de él) | Valores exactos |
| 4 | Este documento | Cómo se aplicó la v2 en el código real |
| 5 | `docs/diseno-v2/maquetas/*.html` | Maquetas de referencia. **Contienen cifras y datos biográficos sin confirmar** («25+», «32.030 votos», «Más de 25 años…»): no se copian al sitio ni a las semillas |
| 6 | `docs/componentes/*.html`, `resources/css/ra-componentes.css` | Kit v1. Se conserva como histórico; **ya no se importa** |

Si este documento y el código difieren, manda el código y este documento se corrige.

## 2. Qué cambió con la v2 y qué no

**Cambió (solo presentación):** tokens y paleta (noche, abismo, marfil, arena, esmeralda, coral, oro, jade), una sola familia (Montserrat 400/600/700/800; se retiró Permanent Marker), botones en píldora y mayúsculas, encabezado oscuro de vidrio, entrada de inicio cinematográfica con la rosa v3 y la cápsula de registro, ejes en rejilla bento, comunas con red de raíces, buzón esmeralda con temas en chips, noticias editoriales, pie noche con «Itagüí» en contorno, cabecera oscura para páginas internas, fondos por bloque con alternancia automática, favicon y manifiesto web.

**No cambió:** rutas, controladores, modelos, migraciones, validaciones, consentimientos, outbox del CRM, caché, CSP, el constructor de páginas (mismos tipos de bloque y mismos datos guardados), banners (mismos campos; se sumó el punto focal), noticias, menú y redes. Las páginas existentes se ven con el diseño nuevo sin editarlas, y las futuras también.

Agregados al panel, todos opcionales y sin migraciones: selector «Fondo» en los bloques que admiten más de uno, cifras confirmadas en la franja «Raíces», texto en el bloque de comunas, punto focal de los banners e imagen de cabecera de las páginas.

## 3. Arquitectura de estilos

| Archivo | Rol |
|---|---|
| `resources/css/tokens.css` | Variables de `tokens-v2.css`, más alias v1 (`--crema`, `--azul-itagui`, `--coral-texto`, `--verde-profundo`, `--verde-raiz`, `--borde-suave`, `--foco`, `--foco-inverso`) para no romper nada que aún los nombre |
| `resources/css/fuentes.css` | `@font-face` de Montserrat 400, 600, 700 y 800 (WOFF2 latino de `@fontsource`) |
| `resources/css/sitio.css` | Punto de entrada único. Importa fuentes y tokens, y contiene todo el diseño en 22 secciones numeradas: base · fondos y contexto oscuro · composición · tipografía · botones · encabezado y menú · formularios · entrada · cabecera interna · lema y rosa · aviso de escucha · ejes · raíces y cifras · comunas · buzón · noticias · agenda · redes · pie · bloques y prosa · cookies · movimiento. Al final, ajustes de pantallas angostas (≤ 359 px y ≤ 479 px) |
| `app/Support/Fondos.php` | Fondos permitidos por bloque, clases CSS y alternancia (§10) |
| `app/Support/Medios.php` | URL de una conversión y punto focal de una imagen (§11) |
| `public/css/montserrat-panel.css` | Montserrat para el panel |

Reglas: todo color, espacio y radio sale de `var(--token)` (excepciones: `errors/500` y `errors/503`, que funcionan sin Vite; el SVG autónomo `public/img/rosa-raices.svg`; los hexadecimales del tema del panel). Prefijo `ra-` y nombres en español. Sin estilos en línea, salvo `object-position` del punto focal. Compilar con `npm run build` (en Docker: `docker run --rm -v "$PWD":/app -w /app node:22 sh -c "npm install && npm run build"`).

## 4. Tokens

Ver la tabla completa en `tokens.css` y §3 de la especificación v2. Papeles:

| Token | Valor | Papel |
|---|---|---|
| `--noche` | `#04151f` | Fondo oscuro principal: encabezado, entrada, redes, pie, cabeceras internas. Texto sobre coral |
| `--abismo` / `--profundo` | | Oscuros secundarios: raíces, citas, cifras |
| `--marfil` / `--blanco` / `--arena` | | Fondos claros (70 % del sitio) |
| `--esmeralda` | `#0f5c45` | Escucha y comunidad: buzón, aviso de escucha, llamados, números de comuna |
| `--coral` | `#f45a43` | Acción: botón principal (texto noche), «me planto.», rosa. Nunca texto pequeño sobre claro |
| `--coral-hondo` | | Coral para texto sobre claro (enlaces, rótulos) |
| `--oro`, `--oro-claro`, `--oro-oscuro` | | Rótulos, filos, enlaces sobre oscuro |
| `--jade` | | Acento de comunidad sobre oscuro (WhatsApp, chips activos) |
| `--marfil-texto`, `--niebla` | | Texto sobre oscuro (principal y secundario) |

## 5. Fondos, contexto oscuro y regla 70/30

- Cada sección lleva `ra-fondo-{nombre}`. Los oscuros (`noche`, `abismo`, `profundo`, `esmeralda`) llevan además `ra-oscuro`, que cambia texto, enlaces (excepto botones), campos, foco y botones secundarios. Un componente nuevo debe verse bien en ambos contextos sin variantes propias.
- `coral` como fondo solo en el llamado; ahí el botón principal pasa a noche.
- Regla 70/30: al guardar una página, si más del 30 % de los bloques visibles queda sobre fondo oscuro, el panel muestra un aviso (no bloquea el guardado).

## 6. Tipografía y composición

- Montserrat en todo. Clases: `ra-etiqueta` (rótulo 11–12 px, 700, espaciado .3em, oro), `ra-lema` (54–92 px, 800; 44 px en ≤ 359 px), `ra-titular-banner`, `ra-display`, `ra-h2`, `ra-h2-medio`, `ra-h3`, `ra-entradilla`, `ra-bajada`, `ra-cita-grande`, `ra-acento` (última palabra en coral).
- `ra-contenedor`: máximo 1240 px, gutter 20 px (16 px en el encabezado bajo 360 px) y 32 px desde 768 px. `ra-seccion`: 56 px de alto de relleno en móvil, 112 px en escritorio.
- Puntos de quiebre: 768 y 980 px (menú completo, entrada en dos columnas, bento de 4 columnas).

## 7. La rosa, las raíces y la marca tipográfica

- **Rosa «Aquí me planto» (desde el 10 oct 2026):** pieza de la campaña `public/img/SCR-20261010-mcyq.jpeg` (rosa roja con raíces turquesa luminosas). Se le quitó el fondo gris conservando el halo («color a alfa» contra el fondo estimado por zonas), se recortó la franja gris de la captura y se exportó en WebP con transparencia: `public/img/marca/rosa-aqui-me-planto-{420,820}.webp`. Está pensada para fondos oscuros.
- `x-ra.rosa-raices` ahora pinta esa imagen (`<img>` con `srcset`). Props: `decorativa` (alt vacío cuando hay texto equivalente), `etiqueta`, `prioridad` (carga temprana en la entrada); `halo` se conserva por compatibilidad. Aparece junto al lema: entrada de inicio, Súmate, gracias, manifiesto y `/enlaces`.
- **Raíces:** pieza `public/img/rosa-raices.png` (brote verde con raíces luminosas). Mismo tratamiento, recortada al brote y las raíces y fundida sobre el color abismo (único fondo del bloque «Raíces»): `public/img/marca/raices-{520,900}.webp`. Se ve en la franja «Raíces» de inicio cuando el bloque no trae foto ni cifras, con un fundido elíptico en los bordes.
- `x-ra.rosa-flor`: solo la flor (SVG v3), para marcadores de imagen de noticias.
- Entrada de inicio: con foto, la rosa va delante a la izquierda de la foto (300 px en escritorio); sin foto, ocupa el alto de la columna, centrada (`object-fit: contain`, máximo 460 px).
- `x-ra.marca`: «ROSA» coral + «ACEVEDO», cargo en rótulo oro. No es un logotipo: cuando Comunicaciones entregue el SVG, se reemplaza aquí.
- Archivos fuera del sitio: `public/img/rosa-raices.svg` (v3) y `public/img/redes-por-defecto.jpg` (de `docs/diseno-v2/piezas/og-redes-1200x630.png`).

## 8. Componentes Blade

| Componente | Notas v2 |
|---|---|
| `encabezado` | Noche casi sólida (va en el flujo, sobre el marfil del cuerpo), filo oro, menú desde 980 px, menú móvil a pantalla completa. Botón con el ajuste `boton_encabezado` |
| `banner` | Entrada: textos a la izquierda, foto a sangre a la derecha con tres fundidos, rosa delante, nombre de Rosa en oro. Varios banners = carrusel (cambian foto, textos y botones). El nombre va 28 px sobre el borde inferior de la foto, dentro de la entrada (nunca sobre la franja de escucha). Prop `corta` para comunas |
| `formulario-registro` | Variante `capsula` (vidrio sobre noche; en escritorio, nombre, celular y botón en una fila) y `claro` (tarjeta blanca en Súmate). Lógica y pasos sin cambios |
| `cabecera` | Cabecera oscura de páginas internas: rótulo, título, entradilla, foto opcional con punto focal. Slot `antes` |
| `lema` | «Aquí / me planto.» con rosa opcional (`ra-lema-fila`) |
| `aviso-escucha` | Franja esmeralda bajo la entrada |
| `ejes` | `bento` (primer eje grande noche, tarjeta final coral) o `lista` |
| `selector-comuna` | Red de raíces (prop `raices`); cada comuna enlaza solo si su página está publicada, si no muestra «Pronto». En todo el sitio las comunas se nombran solo por su número («Comuna 4», `TerritorioComuna::nombrePublico()`); el corregimiento, «Corregimiento El Manzanillo» |
| `buzon` | Temas como chips (radios), variante oscura en esmeralda. El selector de barrio (opcional) solo aparece si hay catálogo cargado; igual en el paso 2 del registro |
| `tarjeta-noticia` / `noticias` | Variantes `destacada`, `fila`, `rejilla`; sin imagen, la rosa-flor como marcador |
| `pie` | Noche, «Itagüí» en contorno; en la franja legal, responsable del tratamiento, cookies y el crédito «Desarrollo: DataFocus S.A.S.» (enlace a www.datafocussas.com, pestaña nueva) |
| `agenda`, `franja-redes`, `redes`, `compartir`, `marca`, `boton`, `campo`, `icono` | Reestilizados; misma interfaz |

## 9. Páginas

- **Inicio:** se arma con los bloques de la página `inicio` del panel (§10). Orden semilla: registro (entrada) → aviso de escucha → ejes → raíces → comunas → buzón → noticias → agenda → redes.
- **Internas** (propuestas, eje, comunas, noticias, noticia, agenda, evento, buzón, Súmate, mis datos, política, transparencia, 404): `x-ra.cabecera` noche + contenido claro.
- **Páginas del panel** (`/{slug}`): cabecera con la imagen «Imagen de cabecera y para redes» si existe → bloques → compartir → llamado esmeralda.
- **Comuna:** con banners de comuna, entrada corta; si no, cabecera.
- **Enlaces** (`/enlaces`): página noche centrada. **500/503:** colores fijos noche/marfil/oro.

## 10. Bloques del constructor y alternancia automática

Los tipos de bloque y sus datos son los mismos de siempre (`app/Filament/Bloques.php`, vistas en `resources/views/partials/bloques/`). `partials/bloques-lista.blade.php` filtra los visibles y llama a `Fondos::resolver()`, que decide el fondo de cada uno:

| Bloque | Fondos permitidos (el primero es el de por defecto) |
|---|---|
| registro | noche |
| raices, cifras | abismo |
| buzon | esmeralda |
| redes, video | noche |
| noticias, imagen, comunas | marfil, blanco |
| agenda | arena, marfil |
| texto | marfil, blanco, arena |
| imagen_texto | marfil, arena, abismo (el antiguo «Verde Raíces» se lee como abismo) |
| cita | abismo, marfil |
| galeria | marfil, arena |
| llamado | esmeralda, noche, coral |
| linea_tiempo, formulario, ejes | marfil |

Reglas, en orden: (1) dos oscuros seguidos: el segundo usa su primer fondo claro permitido; si no tiene, se inserta un separador marfil; (2) dos claros iguales seguidos: el segundo alterna con blanco, arena o marfil según lo permitido. Si un fondo guardado no está permitido, se usa el de por defecto. Pruebas: `tests/Unit/FondosTest.php`.

## 11. Imágenes, punto focal y favicon

- URLs de imágenes **relativas** (`/storage/…`): el disco `public` usa `url => '/storage'`, así la foto se pide al mismo dominio con el que se abrió el sitio (www o sin www, localhost o 127.0.0.1) y la CSP `img-src 'self'` no la bloquea. Donde hace falta una URL completa (`og:image`, datos estructurados) se envuelve en `url()`.
- Bloque «Imagen»: foto completa, centrada, sin recortar, con alto máximo de 760 px.
- Conversiones WebP de medialibrary sin cambios (`w400`, `w800`, `w1200`, `w1600`).
- Punto focal: propiedad personalizada `foco` (`{x, y}` en %) de la imagen; por defecto 62 % / 18 %. Se edita en el banner («Punto focal horizontal/vertical») y se aplica en `object-position` con `Medios::foco()`. Sin columnas nuevas.
- Favicon y manifiesto en `public/` desde `docs/favicon/` (`favicon.ico`, `favicon.svg`, `apple-touch-icon.png`, `icon-192/512`, `site.webmanifest`); `theme-color` `#04151f`.

## 12. Movimiento

`resources/js/sitio.js` agrega `ra-js` al `<html>` y un IntersectionObserver revela los elementos `[data-aparecer]` (subida corta y opacidad). Sin JavaScript o con `prefers-reduced-motion`, todo se ve desde el inicio.

## 13. Accesibilidad

- Auditoría axe (WCAG 2.1 A/AA) en 390 y 1280 px sin violaciones en: inicio, Súmate, buzón, propuestas, eje, mis datos, Conoce a Rosa, comunas, noticias, agenda, manifiesto, enlaces, política de datos y 404.
- Sin desbordamiento horizontal en 320, 360 y 390 px (las tablas largas se desplazan dentro de su caja).
- Foco visible en claro (`--foco`) y en oscuro (`--foco-inverso`); controles de 48 px o más; etiquetas asociadas (en la cápsula se ocultan visualmente, no para lectores); errores escritos.
- Registro completo solo con teclado verificado en Súmate (3 pasos) y en la cápsula de inicio.
- `.ra .ra-oscuro a:not(.ra-btn)`: los enlaces sobre oscuro van en oro claro, pero los botones conservan su color.

## 14. Panel administrable

- Tema: primario esmeralda `#0f5c45`, peligro `#b8321f`, Montserrat; ingreso sobre fondo noche con filo coral; favicon de la rosa.
- Bloques: selector «Fondo» cuando hay más de uno permitido; «Cifras (opcional)» en Raíces (máximo 3, solo cifras confirmadas con fuente, nunca de la Encuesta Itagüí 2026); «Texto» en el selector de comunas.
- Banners: punto focal. Páginas: «Imagen de cabecera y para redes» (colección `og`).
- Aviso al guardar una página con más del 30 % de bloques oscuros.

## 15. Cómo modificar el diseño (procedimientos)

1. **Color o token:** editar `docs/diseno-v2/tokens-v2.css` y copiarlo a `resources/css/tokens.css`; regenerar `docs/tokens.json`; verificar contraste (4,5:1 texto, 3:1 texto grande y controles); si aplica, `AdminPanelProvider`.
2. **Componente nuevo:** `resources/views/components/ra/{nombre}.blade.php` con `@props` y comentario; estilos `ra-{nombre}` en la sección que corresponda de `sitio.css`; que funcione con y sin `ra-oscuro`; agregarlo a §8.
3. **Bloque nuevo:** `Block::make('tipo')` con `activo` (y `...self::fondo('tipo')` si admite varios fondos) en `Bloques.php`; sus fondos en `Fondos::PERMITIDOS`; vista en `partials/bloques/{tipo}.blade.php` usando `$clasesFondo`; agregarlo a §10.
4. **Página nueva:** desde el panel, sin código (Contenido → Páginas). Las rutas propias del código van en `Pagina::RESERVADAS`.
5. **Rosa, raíces, lema o logotipo de Comunicaciones:** la rosa se cambia reemplazando `public/img/marca/rosa-aqui-me-planto-*.webp` (fondo transparente, mismas proporciones o ajustando `width`/`height` en `rosa-raices.blade.php`); las raíces, `public/img/marca/raices-*.webp`. El lettering va en `lema.blade.php` con `role="img"` y `aria-label="Aquí me planto."`.
6. **Antes de entregar:** `npm run build`, `vendor/bin/pint`, `php artisan test`; revisión visual en 390, 1280 y 1440 px (desplazando la página para que aparezcan las secciones); axe de las páginas de §13; actualizar este documento en el mismo commit.

## 16. Decisiones registradas y pendientes

| Fecha | Decisión | Estado |
|---|---|---|
| 6 oct 2026 | Kit `ra-` v1 y Montserrat autoalojada | Reemplazado por la v2 (el kit queda como histórico) |
| 8 oct 2026 | Verde como color de escucha y comunidad; aviso de escucha bajo la entrada | Vigente en v2 como esmeralda |
| 9 oct 2026 | Diseño v2 «Noche de raíces» en todo el sitio público y tema del panel | Vigente |
| 9 oct 2026 | Fondos por bloque con alternancia automática y aviso 70/30 | Vigente |
| 9 oct 2026 | Permanent Marker retirado; el lema va en Montserrat 800 | Vigente hasta el lettering SVG |
| 9 oct 2026 | Las cifras y datos de las maquetas v2 no se publican; Raíces muestra cifras solo si el panel las carga | Vigente |
| 10 oct 2026 | Rosa y raíces: piezas raster de la campaña en lugar de la rosa SVG v3 (§7) | Vigente |
| 10 oct 2026 | Comunas nombradas solo por su número en el sitio | Vigente |
| Pendiente | Logotipo y lettering «Aquí me planto» en SVG | Comunicaciones |
| Pendiente | Fotografías definitivas (16:9 y 4:5) para banners y cabeceras | Comunicaciones |
| Pendiente | Hoja de vida oficial para Raíces y «Conoce a Rosa» | Campaña |
