import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/sitio.css', 'resources/js/sitio.js'],
            refresh: true,
        }),
    ],
    build: {
        // Hostinger sirve los archivos estáticos; Cloudflare los cachea.
        assetsInlineLimit: 0,
    },
});
