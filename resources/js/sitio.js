// Sitio público: Alpine.js para formularios por pasos, menú móvil, carrusel y cookies. Sin frameworks pesados.
import Alpine from 'alpinejs';
import registro from './registro';
import cookies from './cookies';
import { capturarOrigen, conversion } from './origen';

window.Alpine = Alpine;
Alpine.data('registro', registro);
Alpine.data('cookies', cookies);

capturarOrigen();

// Eventos de conversión propios (A/B, clic en WhatsApp, compartir) con data-conversion="tipo".
document.addEventListener('click', (evento) => {
    const enlace = evento.target.closest('[data-conversion]');
    if (enlace) conversion(enlace.dataset.conversion);
});

Alpine.start();
