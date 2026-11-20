// Carrusel de banners (diseño v2, §6.2): una diapositiva por foto, cada una con sus propios segundos (panel →
// Banners → «Cambiar de foto cada»). Se detiene mientras el puntero o el foco están encima, cuando la pestaña no
// se ve y con el botón de pausa (WCAG 2.2.2). Con «reducir movimiento» empieza en pausa: se cambia con los puntos.
export default (segundos = [], bannerDe = []) => ({
    actual: 0,
    pausado: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
    encima: false,
    temporizador: null,

    init() {
        this.programar();
    },

    destroy() {
        clearTimeout(this.temporizador);
    },

    banner() {
        return bannerDe[this.actual] ?? 0;
    },

    programar() {
        clearTimeout(this.temporizador);
        if (this.pausado || segundos.length < 2) return;
        this.temporizador = setTimeout(() => {
            if (!this.encima && !document.hidden) this.actual = (this.actual + 1) % segundos.length;
            this.programar();
        }, (segundos[this.actual] || 6) * 1000);
    },

    ir(indice) {
        this.actual = indice;
        this.programar();
    },

    alternar() {
        this.pausado = !this.pausado;
        this.programar();
    },

    detener(valor) {
        this.encima = valor;
    },
});
