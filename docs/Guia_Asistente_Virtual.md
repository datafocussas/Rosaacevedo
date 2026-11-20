# Guía del asistente virtual

Para el equipo de Comunicaciones y Tecnología. Explica qué es el asistente del sitio, cómo se enciende, cómo se le enseña y qué cuidados tener.

## Qué es

Es un chat que aparece en todas las páginas del sitio con el botón **«Pregúntale al asistente»**. La gente le pregunta por Rosa, su trayectoria, sus propuestas o su visión de Itagüí, y el asistente responde y sostiene la conversación.

Por dentro, el sitio le manda cada pregunta a **Claude**, un modelo de lenguaje de la empresa Anthropic, junto con dos cosas:

1. **Las reglas fijas** (en el código; no se cambian desde el panel):
   - Responder solo con la base de conocimiento.
   - No inventar datos de Rosa.
   - Decir «esa información todavía no la tengo» cuando falte algo.
   - No dar cifras de encuestas.
   - No pedir el voto en precampaña.
   - No pedir datos personales.
   - No hablar de otros candidatos.
   - Tutear, sin emojis y con respuestas breves.
2. **La base de conocimiento**: lo que el equipo carga en el panel. **Es lo único que el asistente sabe de Rosa.**

Si un dato no está en la base, el asistente no lo sabe. Si un dato está mal en la base, el asistente lo repetirá. Por eso la base se trata con el mismo cuidado que un texto publicado.

## Encenderlo (una sola vez; lo hace quien administra el sistema)

1. **Crear la API key de Anthropic.**
   - Entra a **console.anthropic.com** con la cuenta de la campaña.
   - Carga un saldo en **Billing**: cada pregunta tiene un costo pequeño que se descuenta de ahí.
   - En **API Keys → Create Key**, ponle un nombre como «sitio rosaacevedo.com» y copia la llave. Empieza por `sk-ant-` y solo se ve una vez.
2. En el panel, ve a **Asistente → Configuración**:
   - Pega la llave en **API key de Anthropic**. Se guarda cifrada y nadie la puede volver a ver.
   - Revisa el **nombre**, el **mensaje de bienvenida** y las **preguntas sugeridas**.
   - Deja apagado **«Mostrar el asistente en el sitio»** hasta cargar la base.
   - Guarda.
3. Carga la base de conocimiento (sección siguiente).
4. Arriba a la derecha, usa **Probar el asistente**. Haz varias preguntas, incluidas algunas cuya respuesta no esté en la base: debe decir que no la tiene, no inventarla.
5. Cuando estés conforme, enciende **«Mostrar el asistente en el sitio»** y guarda.

## Enseñarle: la base de conocimiento (Asistente → Base de conocimiento)

La base está hecha de **fichas**. Cada ficha tiene un tema, una categoría (trayectoria, propuestas, visión, comunas, campaña, preguntas frecuentes u otro), el texto y la **fuente**.

**Dos formas de cargar:**
- **Nueva ficha:** escribir o pegar el texto.
- **Importar documento:** subir un PDF, un Word (.docx), un .txt o un .md. El sitio extrae el texto y crea una ficha **apagada**, para revisarla, corregirla y encenderla. Los PDF escaneados (fotos de papel) no tienen texto: hay que copiarlo a mano.

**Cómo escribir buenas fichas:**
- Frases completas, como se le explicaría a una persona: «Rosa fue concejala de Itagüí entre 20XX y 20XX por el partido…».
- Un tema por ficha. Es mejor «Trayectoria en el Concejo» y «Formación académica» por separado que un documento de 40 páginas.
- **Solo datos confirmados por la campaña, con su fuente.** Si falta un dato, escribe `[POR CONFIRMAR]`: el asistente dirá que aún no está confirmado.
- **Nunca cifras de la Encuesta Itagüí 2026** ni datos internos.
- Las preguntas frecuentes funcionan muy bien así: «¿Rosa tiene partido? Sí, …».
- Si en **Configuración** está encendido «Usar también los ejes de propuestas publicados», el asistente ya lee los ejes publicados con sus compromisos. No hace falta copiarlos.

**El interruptor «Activa»** decide si el asistente usa la ficha. Sirve para preparar textos sin publicarlos.

**Tamaño:** en Configuración se ve cuántos «tokens» tiene la base (un token es más o menos media palabra). Hasta unos 100.000 tokens funciona bien. Si aparece el aviso rojo, la base está demasiado grande: cada respuesta será lenta y costosa, así que conviene dejar solo lo esencial.

## Tono (Asistente → Configuración → Tono)

Ahí van **indicaciones de estilo**, no datos. Por ejemplo: «Habla con calidez, como alguien del barrio. Si preguntan por seguridad, menciona el eje de convivencia.»

Las reglas fijas mandan sobre estas indicaciones.

## Ver qué pregunta la gente (Asistente → Conversaciones)

- Se ve cada conversación: cuándo empezó, desde qué página y qué se preguntó.
- El filtro **«Con respuestas fallidas o límites»** muestra dónde el asistente no pudo responder.
- Revisar las conversaciones cada semana es la mejor forma de saber **qué falta en la base**. Si mucha gente pregunta algo que el asistente no sabe, hay que agregar esa ficha.
- Solo las ven quienes tienen permiso de ver registros (Administrador y Analista), porque la gente a veces escribe datos personales aunque se le pida que no lo haga.

## Privacidad

- El chat **no pide** nombre, celular ni correo. El aviso bajo el campo de texto dice que no se compartan datos personales.
- Se guarda el texto de la conversación, la página desde la que se abrió y una huella de la conexión (no la dirección IP).
- Las conversaciones **se borran solas** a los días configurados en «Días que se guardan las conversaciones» (90 por defecto).
- **Antes de encenderlo:** el texto de la política de tratamiento de datos debe mencionar el asistente: qué se guarda, para qué, cuánto tiempo, y que las preguntas se procesan con un proveedor externo (Anthropic, empresa de Estados Unidos). Lo aprueba el área jurídica en **Sitio → Políticas** con una versión nueva.

## Costos y límites

- **Modelo:**
  - **Claude Opus 5.5** da las mejores respuestas.
  - **Sonnet 5.5** y **Haiku 5.5** son más económicos.
  - Se puede cambiar en cualquier momento desde Configuración.
- **Esfuerzo:** «Bajo» basta para conversar y es lo más rápido.
- **Límites:**
  - **Preguntas por conversación:** 30 por defecto.
  - **Preguntas por día en todo el sitio:** 2.000 por defecto. Es el tope de gasto: al llegar, el asistente descansa hasta el día siguiente.
  - **Por conexión:** máximo 8 preguntas por minuto y 60 por hora.
- En Configuración se ven las preguntas del día y los tokens de los últimos 30 días. El costo exacto en dinero está en console.anthropic.com → Usage.

## Si algo falla

| Lo que pasa | Qué revisar |
|---|---|
| El botón no aparece en el sitio | Configuración: que esté encendido y que diga que la API key está guardada |
| Responde «En este momento no puedo responder» | La API key (¿se borró o venció en Anthropic?), el saldo en console.anthropic.com o una caída de Anthropic. El sitio sigue funcionando normal |
| Dice que no sabe algo que sí está | Que la ficha esté **Activa** y que el dato esté escrito con claridad |
| Responde algo incorrecto | Buscar el dato en la base y corregir la ficha. Si no está en la base, contárselo a Tecnología con el enlace de la conversación |
