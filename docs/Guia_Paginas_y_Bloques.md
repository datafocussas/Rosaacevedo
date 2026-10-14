# Guía de páginas y bloques del panel

Para el equipo de Comunicaciones y Tecnología. Explica cómo se arma una página desde el panel (`/admin` → Contenido → Páginas), qué hace cada bloque, qué campos tiene y, en los formularios, a dónde va la información. Todos los bloques tienen una prueba automática que arma una página con ellos y verifica que se pinten (`tests/Feature/BloquesPaginaTest.php`).

## Cómo funciona una página

1. **Crear:** Contenido → Páginas → Crear. Escribe el título; la dirección (`/nuestro-equipo`) se llena sola.
2. **Estado:** solo las páginas en **Publicada** se ven en el sitio. En Borrador no existen para el público.
3. **Menú:** en la sección «Menú» decides si aparece en el menú principal (máximo 6 ítems) o en el pie.
4. **Bloques:** la página es una lista de bloques, uno debajo de otro. Se agregan con «Agregar bloque», se reordenan con las flechas, se duplican y se borran. El interruptor **Visible** oculta un bloque sin borrarlo.
5. **Fondo:** los bloques que admiten más de un fondo tienen el selector «Fondo». Si dos bloques seguidos quedan con el mismo fondo, el sitio alterna el segundo solo. Si más del 30 % de la página queda oscura, el panel avisa al guardar.
6. **Cabecera, SEO y redes:** la «Imagen de cabecera y para redes» aparece a la derecha de la cabecera oscura y al compartir la página en WhatsApp o redes. El título y la descripción SEO son lo que muestra Google.
7. Toda página termina con los botones de compartir y un llamado «Aquí me planto contigo» hacia Súmate.

## Bloques de contenido

| Bloque | Campos | Qué hace en la página | Notas |
|---|---|---|---|
| **Texto** | Título (opcional), Contenido (editor con negrita, cursiva, enlaces, títulos, listas y cita) | Columna de lectura centrada | Para textos largos. Escribe `[POR CONFIRMAR]` donde falte un dato: se resalta para que nadie lo publique sin verificar |
| **Imagen** | Imagen, Texto alternativo (obligatorio), Pie de foto | Foto completa y centrada, sin recortes, con alto máximo | JPG o PNG hasta 4 MB (mejor menos de 200 KB). El texto alternativo describe la foto a quien no ve |
| **Imagen + texto** | Imagen, Texto alternativo, Rótulo, Título, Texto, «Imagen a la derecha», Fondo | Foto a un lado y texto al otro; en celular, uno debajo del otro | El texto alternativo es obligatorio si hay imagen |
| **Cita** | Texto, Autor, Fondo | Frase grande entre comillas | |
| **Línea de tiempo** | Rótulo, Título, Hitos (periodo, cargo o hito, detalle) | Trayectoria con una línea vertical | Solo con datos de la hoja de vida oficial |
| **Video** | Enlace de YouTube o Vimeo, Título | Portada con botón de reproducir; el video carga al hacer clic | Si el enlace no es de YouTube o Vimeo, el bloque no aparece |
| **Galería** | Título, Imágenes (varias, reordenables), Texto alternativo | Rejilla de fotos; con 5 o más, la primera va grande | |
| **Cifras** | Hasta 4 pares valor y texto | Tarjetas con número grande | Solo cifras confirmadas con fuente. Nunca de la Encuesta Itagüí 2026 |
| **Llamado a la acción** | Rótulo, Título, Texto, Texto y enlace del botón, Fondo (esmeralda, noche o coral) | Franja con texto y un botón | El enlace puede ser interno (`/sumate`) o externo |
| **Lista de ejes** | Rótulo, Título, Texto, Texto del botón | Rejilla de los 7 ejes de propuestas | Toma los ejes publicados en «Ejes y propuestas». Si no hay ninguno publicado, no aparece |
| **Selector de comunas** | Rótulo, Título, Texto, Fondo | Las 7 comunas y el corregimiento | Cada comuna enlaza a su página solo si está publicada; si no, dice «Pronto» |

## Bloque Formulario: cómo funciona y dónde se guarda

Tiene dos tipos. Los dos funcionan igual que los de `/sumate` y `/buzon`: misma validación, mismos consentimientos y mismo envío al CRM. El sitio guarda además desde qué página llegó cada dato, en el campo «página» de las interacciones del registro.

### Tipo «Registro (Súmate)»

| Paso | Qué pide | Dónde queda |
|---|---|---|
| 1 | Nombre, celular, autorización de datos (obligatoria) y WhatsApp (opcional) | Tabla `ciudadanos`. El celular se guarda cifrado y con una huella para no duplicar: si la persona ya existía, se actualiza su registro. Cada autorización queda en `consentimientos` con versión del texto, IP, navegador, formulario y hora |
| 2 | Correo (opcional), **comuna (obligatoria)** y barrio (opcional, si hay catálogo cargado) | Se completa el mismo registro. Si elige barrio, la comuna sale del barrio |
| 3 | ¿Cómo quieres ayudar?, ¿cuándo puedes?, puesto de votación (opcional), afinidad política (opcional, dato sensible) | Tabla `voluntariado`; el registro queda marcado como voluntario |

La persona puede detenerse con «Ahora no» en el paso 2 o en el 3: lo que ya envió queda guardado.

**En el panel:** Ciudadanía → Registros. La ficha muestra nombre, celular, comuna, barrio, paso alcanzado, cómo quiere ayudar, disponibilidad, puesto de votación, consentimientos con evidencia, origen (UTM, código QR, página) y el estado del envío al CRM.

### Tipo «Buzón ciudadano»

Pide tema, **comuna (obligatoria)**, barrio (opcional), propuesta (hasta 1.500 caracteres), foto (opcional, hasta 5 MB), nombre, celular, autorización de datos y permiso para publicarla sin nombre.

- La propuesta queda en `propuestas_ciudadanas` con un código `PR-AAAA-NNNN`, y la persona se registra (o se actualiza) en `ciudadanos`.
- La foto se guarda en un disco privado: no tiene dirección pública y solo se ve en el panel.
- **En el panel:** Ciudadanía → Propuestas ciudadanas. Ahí se ve la foto, se cambia el estado, se asigna, se responde y se marca como destacada si la persona lo autorizó.

### Envío al CRM

Cada registro, cada paso completado y cada propuesta se anotan en una cola de envíos (`crm_outbox`). Si la conexión está activa (Sitio → Conexión con el CRM), cada minuto se envía lo pendiente. Si el CRM falla, se reintenta a los 1, 5, 15 y 60 minutos y después cada 6 horas, sin perder nada. El estado de cada registro (pendiente, enviado con éxito o con error) se ve en su ficha.

## Bloques propios de la página de inicio

Solo aparecen al editar la página «Inicio».

| Bloque | Qué hace |
|---|---|
| **Inicio · Entrada con registro** | La entrada oscura con «Aquí me planto.», la rosa, los banners activos (módulo Banners) y el formulario de registro de 3 pasos |
| **Inicio · Franja «Raíces»** | Cita grande con texto y enlace al manifiesto; a la derecha la ilustración de raíces, o la foto o las cifras si se cargan |
| **Inicio · Llamado al buzón** | Franja verde con el formulario del buzón |
| **Inicio · Últimas noticias** | Las 4 noticias publicadas más recientes (si no hay, no aparece) |
| **Inicio · Próximos encuentros** | Los 3 próximos eventos públicos de la agenda (si no hay, no aparece) |
| **Inicio · Franja de redes** | Hashtag y enlaces a las redes activas (módulo Redes) |
