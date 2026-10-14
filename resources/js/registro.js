// Formulario progresivo de registro (RF-01). Paso 1 → POST /api/v1/registro; pasos 2 y 3 → PATCH
// /api/v1/registro/{token}. El token firmado de un solo uso se guarda en localStorage.
import { origen, conversion } from './origen';

const CLAVE_TOKEN = 'ra-registro';

function guardarToken(valor) {
    try {
        if (valor) localStorage.setItem(CLAVE_TOKEN, JSON.stringify(valor));
        else localStorage.removeItem(CLAVE_TOKEN);
    } catch {
        // Sin almacenamiento local: el flujo sigue en memoria.
    }
}

export default ({ pasos = 3, api, comuna = null }) => ({
    paso: 1,
    pasos,
    token: null,
    codigo: '',
    whatsapp: null,
    terminado: false,
    enviando: false,
    errores: {},
    errorGeneral: '',
    barrios: [],
    datos: { email: '', comuna_id: comuna ? String(comuna) : '', barrio_id: '', intereses: [], disponibilidad: [], puesto_votacion: '', consent_afinidad: false },
    titulos: { 2: 'Cuéntanos de tu barrio', 3: '¿Quieres ayudar?' },
    botones: { 2: 'Continuar', 3: 'Quiero ser voluntario' },

    async enviar() {
        if (this.enviando) return;
        this.errores = {};
        this.errorGeneral = '';
        this.enviando = true;

        try {
            if (this.paso === 1) await this.enviarPaso1();
            else await this.completar();
        } catch (error) {
            this.errorGeneral = 'No pudimos enviar tus datos. Revisa tu conexión e inténtalo de nuevo.';
        } finally {
            this.enviando = false;
        }
    },

    async enviarPaso1() {
        const formulario = new FormData(this.$refs.form);
        const cuerpo = {
            nombre: formulario.get('nombre') || '',
            celular: formulario.get('celular') || '',
            consent: {
                general: formulario.get('consent[general]') === '1',
                whatsapp: formulario.get('consent[whatsapp]') === '1',
            },
            sitio_web: formulario.get('sitio_web') || '',
            turnstile: formulario.get('cf-turnstile-response') || '',
            origen: { ...origen(), comuna_pagina: comuna, codigo_q: formulario.get('origen[codigo_q]') || null, evento: formulario.get('origen[evento]') || null },
        };

        const respuesta = await this.peticion('POST', `${api}/registro`, cuerpo);
        if (!respuesta) return;

        this.token = respuesta.token;
        this.codigo = respuesta.codigo;
        this.whatsapp = respuesta.whatsapp || null;
        guardarToken({ token: this.token, codigo: this.codigo, whatsapp: this.whatsapp, paso: 2 });
        conversion('registro_paso_1');
        this.avanzar();
    },

    async completar() {
        const cuerpo = this.paso === 2
            ? { email: this.datos.email || null, comuna_id: this.datos.comuna_id || null, barrio_id: this.datos.barrio_id || null }
            : {
                intereses: this.datos.intereses,
                disponibilidad: this.datos.disponibilidad,
                puesto_votacion: this.datos.puesto_votacion || null,
                consent: { afinidad: !!this.datos.consent_afinidad },
            };
        cuerpo.origen = origen();

        const respuesta = await this.peticion('PATCH', `${api}/registro/${encodeURIComponent(this.token)}`, cuerpo);
        if (!respuesta) return;

        conversion(`registro_paso_${this.paso}`);
        this.token = respuesta.token || null;
        guardarToken(this.token ? { token: this.token, codigo: this.codigo, whatsapp: this.whatsapp, paso: this.paso + 1 } : null);
        this.avanzar();
    },

    async peticion(metodo, url, cuerpo) {
        const respuesta = await fetch(url, {
            method: metodo,
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify(cuerpo),
        });

        if (respuesta.status === 422) {
            const json = await respuesta.json();
            Object.entries(json.errors || {}).forEach(([campo, mensajes]) => {
                this.errores[campo] = mensajes[0];
            });
            if (this.errores.token) {
                guardarToken(null);
                this.errorGeneral = this.errores.token;
            } else if (this.errores.turnstile || this.errores.sitio_web) {
                this.errorGeneral = this.errores.turnstile || this.errores.sitio_web;
            }
            this.$nextTick(() => this.$refs.form.querySelector('[aria-invalid="true"]')?.focus());
            return null;
        }

        if (respuesta.status === 429) {
            this.errorGeneral = 'Recibimos muchos envíos desde tu conexión. Espera unos minutos e inténtalo de nuevo.';
            return null;
        }

        if (!respuesta.ok) throw new Error(`HTTP ${respuesta.status}`);

        return respuesta.json();
    },

    async avanzar() {
        this.paso += 1;
        if (this.paso === 2 && this.barrios.length === 0) await this.cargarBarrios();
        if (this.paso > this.pasos) {
            this.terminado = true;
            guardarToken(null);
        }
        this.$nextTick(() => this.$refs.titulo.focus());
    },

    omitir() {
        this.terminado = true;
        this.paso = this.pasos + 1;
        guardarToken(null);
        this.$nextTick(() => this.$refs.titulo.focus());
    },

    async cargarBarrios() {
        try {
            const respuesta = await fetch(`${api}/territorio/barrios`, { headers: { Accept: 'application/json' } });
            const lista = await respuesta.json();
            const grupos = new Map();
            lista.forEach((barrio) => {
                const nombre = barrio.comuna.nombre;
                if (!grupos.has(nombre)) grupos.set(nombre, []);
                grupos.get(nombre).push(barrio);
            });
            this.barrios = [...grupos].map(([nombre, items]) => ({ comuna: nombre, comunaId: String(items[0].comuna.id), items }));
            if (comuna) {
                // Comuna preseleccionada en las páginas territoriales: sus barrios primero.
                const propia = (grupo) => (grupo.items[0].comuna.id === comuna ? 1 : 0);
                this.barrios.sort((a, b) => propia(b) - propia(a));
            }
        } catch {
            this.barrios = [];
        }
    },

    // Con una comuna elegida, el selector de barrio solo muestra los de esa comuna.
    barriosVisibles() {
        return this.datos.comuna_id ? this.barrios.filter((g) => g.comunaId === String(this.datos.comuna_id)) : this.barrios;
    },

    init() {
        // Retoma un registro a medias del mismo navegador (token aún vigente).
        try {
            const guardado = JSON.parse(localStorage.getItem(CLAVE_TOKEN) || 'null');
            if (guardado && guardado.token && guardado.paso <= this.pasos) {
                this.token = guardado.token;
                this.codigo = guardado.codigo;
                this.whatsapp = guardado.whatsapp;
                this.paso = guardado.paso;
                if (this.paso === 2) this.cargarBarrios();
            }
        } catch {
            // Ignorar.
        }
        this.titulos[this.pasos + 1] = 'Gracias por sumarte';
    },
});
