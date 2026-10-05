// Origen del visitante (RF-03): utm_* y referrer de la primera página, guardados en sessionStorage
// y copiados a los campos ocultos de cada formulario.
const CAMPOS = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content'];
const CLAVE = 'ra-origen';

function leer() {
    try {
        return JSON.parse(sessionStorage.getItem(CLAVE) || '{}');
    } catch {
        return {};
    }
}

export function origen() {
    const datos = leer();
    datos.pagina = window.location.pathname;
    datos.variante = document.body.dataset.variante || null;
    return datos;
}

export function capturarOrigen() {
    const parametros = new URLSearchParams(window.location.search);
    const datos = leer();
    let cambio = false;

    CAMPOS.forEach((campo) => {
        const valor = parametros.get(campo);
        if (valor && !datos[campo]) {
            datos[campo] = valor.slice(0, 120);
            cambio = true;
        }
    });

    if (!datos.referrer && document.referrer && !document.referrer.startsWith(window.location.origin)) {
        datos.referrer = document.referrer.slice(0, 255);
        cambio = true;
    }

    if (cambio) {
        try {
            sessionStorage.setItem(CLAVE, JSON.stringify(datos));
        } catch {
            // Navegación privada: el servidor conserva el origen en la sesión.
        }
    }

    document.querySelectorAll('input[data-origen]').forEach((campo) => {
        if (!campo.value && datos[campo.dataset.origen]) campo.value = datos[campo.dataset.origen];
    });
}

export function conversion(tipo) {
    const api = document.body.dataset.api;
    const cuerpo = JSON.stringify({ tipo, variante: document.body.dataset.variante || null, pagina: window.location.pathname });

    if (navigator.sendBeacon) {
        navigator.sendBeacon(`${api}/conversion`, new Blob([cuerpo], { type: 'application/json' }));
    } else {
        fetch(`${api}/conversion`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: cuerpo, keepalive: true });
    }

    if (window.umami) window.umami.track(tipo);
}
