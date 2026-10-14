// Sitio público: Alpine.js para formularios por pasos, menú móvil, carrusel y cookies. Sin frameworks pesados.
import Alpine from 'alpinejs';
import registro from './registro';
import cookies from './cookies';
import { capturarOrigen, conversion } from './origen';

window.Alpine = Alpine;
Alpine.data('registro', registro);
Alpine.data('cookies', cookies);

capturarOrigen();

// Movimiento sereno (diseño v2, §11): aparición por opacidad y 12 px al entrar en pantalla, una sola vez.
// Se desactiva con prefers-reduced-motion y sin IntersectionObserver todo se ve de una vez.
if ('IntersectionObserver' in window && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    document.documentElement.classList.add('ra-js');
    const observador = new IntersectionObserver((entradas) => {
        entradas.forEach((entrada) => {
            if (entrada.isIntersecting) {
                entrada.target.classList.add('ra-visible');
                observador.unobserve(entrada.target);
            }
        });
    }, { rootMargin: '0px 0px -8% 0px' });
    document.querySelectorAll('[data-aparecer]').forEach((elemento) => observador.observe(elemento));
}

// Eventos de conversión propios (A/B, clic en WhatsApp, compartir) con data-conversion="tipo".
document.addEventListener('click', (evento) => {
    const enlace = evento.target.closest('[data-conversion]');
    if (enlace) conversion(enlace.dataset.conversion);
});

Alpine.start();
