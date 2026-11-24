// Publicaciones de redes dentro del texto: el marco de la red se carga solo si la persona aceptó las cookies de
// redes sociales (o las acepta ahora), o si hace clic en «Mostrar la publicación aquí».
import { preferenciasCookies } from './cookies';

export default () => ({
    cargar: Boolean(preferenciasCookies()?.redes),

    init() {
        document.addEventListener('ra:cookies', (evento) => {
            if (evento.detail?.redes) this.cargar = true;
        });
    },
});
