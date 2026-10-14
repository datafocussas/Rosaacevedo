# Favicon · flor de la rosa

Cabeza de la rosa «Raíz de poder» (rediseño del 9 de octubre de 2026). Tiene una base sólida detrás de los pétalos para que se lea como flor a 16 px.

## Archivos (copiar a `public/`)

| Archivo | Uso |
|---|---|
| `favicon.svg` | Navegadores modernos (fondo transparente) |
| `favicon.ico` | Respaldo: 16, 32 y 48 px |
| `favicon-16.png`, `favicon-32.png`, `favicon-48.png` | PNG sueltos |
| `apple-touch-icon.png` | 180 × 180, fondo noche `#04151F` (iPhone y iPad) |
| `icon-192.png`, `icon-512.png` | Android y manifiesto |
| `icon-512-maskable.png` | Ícono adaptable de Android (zona segura) |
| `site.webmanifest` | Manifiesto web |

## En el layout (`resources/views/components/layouts/sitio.blade.php`, dentro de `<head>`)

```html
<link rel="icon" href="/favicon.ico" sizes="48x48">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">
<link rel="manifest" href="/site.webmanifest">
<meta name="theme-color" content="#04151f">
```

Para el panel Filament: `->favicon(asset('favicon.svg'))` en `AdminPanelProvider`.
