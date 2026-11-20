// Asistente virtual del sitio: chat con /api/v1/asistente. La conversación vive en sessionStorage (se conserva al
// cambiar de página y se borra al cerrar la pestaña). Las respuestas se muestran como texto: solo las rutas del
// sitio (/sumate, /buzon…) se vuelven enlaces.
const CLAVE = 'ra-asistente';

const escapar = (texto) => texto.replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);

export default (config = {}) => ({
    config,
    abierto: false,
    entrada: '',
    cargando: false,
    error: '',
    mensajes: [],
    conversacion: null,

    init() {
        try {
            const guardado = JSON.parse(sessionStorage.getItem(CLAVE) || 'null');
            if (guardado) {
                this.mensajes = guardado.mensajes || [];
                this.conversacion = guardado.conversacion || null;
                this.abierto = Boolean(guardado.abierto);
            }
        } catch (e) { /* sin almacenamiento: empieza vacío */ }
    },

    guardar() {
        try {
            sessionStorage.setItem(CLAVE, JSON.stringify({ mensajes: this.mensajes.slice(-40), conversacion: this.conversacion, abierto: this.abierto }));
        } catch (e) { /* sin almacenamiento */ }
    },

    abrir() {
        this.abierto = true;
        this.guardar();
        this.$nextTick(() => { this.bajar(); this.$refs.entrada.focus(); });
    },

    cerrar() {
        this.abierto = false;
        this.guardar();
        this.$nextTick(() => this.$refs.boton.focus());
    },

    nueva() {
        this.mensajes = [];
        this.conversacion = null;
        this.error = '';
        this.guardar();
        this.$refs.entrada.focus();
    },

    bajar() {
        this.$nextTick(() => { this.$refs.lista.scrollTop = this.$refs.lista.scrollHeight; });
    },

    formato(texto) {
        return escapar(texto)
            .replace(/(^|[\s(«"])(\/[a-z0-9-]+(?:\/[a-z0-9-]+)*)(?=[\s).,;:»"]|$)/g, '$1<a href="$2">$2</a>')
            .replace(/\n/g, '<br>');
    },

    async enviar(texto = null) {
        const mensaje = (texto ?? this.entrada).trim();
        if (!mensaje || this.cargando) return;

        this.error = '';
        this.entrada = '';
        this.mensajes.push({ rol: 'usuario', texto: mensaje });
        this.cargando = true;
        this.bajar();

        try {
            const respuesta = await fetch(`${document.body.dataset.api}/asistente`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ mensaje, conversacion: this.conversacion, pagina: window.location.pathname }),
            });
            const datos = await respuesta.json().catch(() => ({}));

            if (respuesta.status === 429) {
                this.error = 'Hiciste muchas preguntas seguidas. Espera un minuto e inténtalo de nuevo.';
            } else if (!respuesta.ok) {
                this.error = datos.message || 'No pudimos enviar tu pregunta. Inténtalo de nuevo.';
            } else {
                this.conversacion = datos.conversacion;
                this.mensajes.push({ rol: 'asistente', texto: datos.respuesta });
            }
        } catch (e) {
            this.error = 'Sin conexión. Revisa tu internet e inténtalo de nuevo.';
        }

        if (this.error) {
            this.mensajes.pop();
            this.entrada = mensaje;
        }
        this.cargando = false;
        this.guardar();
        this.bajar();
        this.$nextTick(() => this.$refs.entrada.focus());
    },
});
