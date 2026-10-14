// Preferencias de cookies (sección 07). Se guardan 6 meses en una cookie técnica de primera parte.
const NOMBRE = 'ra_cookies';
const SEIS_MESES = 60 * 60 * 24 * 182;

export function preferenciasCookies() {
    const valor = document.cookie.split('; ').find((c) => c.startsWith(`${NOMBRE}=`));
    if (!valor) return null;
    try {
        return JSON.parse(decodeURIComponent(valor.split('=')[1]));
    } catch {
        return null;
    }
}

export default () => ({
    visible: preferenciasCookies() === null,
    configurando: false,
    redes: false,
    publicidad: false,

    init() {
        document.addEventListener('click', (evento) => {
            if (evento.target.closest('[data-abrir-cookies]')) {
                const actuales = preferenciasCookies() || {};
                this.redes = !!actuales.redes;
                this.publicidad = !!actuales.publicidad;
                this.configurando = true;
                this.visible = true;
            }
        });
    },

    guardar(todas) {
        const eleccion = {
            redes: todas === null ? this.redes : todas,
            publicidad: todas === null ? this.publicidad : todas,
            fecha: new Date().toISOString(),
        };
        const seguro = window.location.protocol === 'https:' ? '; Secure' : '';
        document.cookie = `${NOMBRE}=${encodeURIComponent(JSON.stringify(eleccion))}; Max-Age=${SEIS_MESES}; Path=/; SameSite=Lax${seguro}`;
        this.visible = false;
        this.configurando = false;
        document.dispatchEvent(new CustomEvent('ra:cookies', { detail: eleccion }));
    },
});
