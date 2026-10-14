<?php

namespace App\Http\Controllers;

use App\Models\PropuestaCiudadana;

/**
 * Foto adjunta a una propuesta del buzón. Vive en el disco privado (no tiene URL pública):
 * solo la ve, desde el panel, quien tenga el permiso de ver propuestas.
 */
class FotoPropuestaController extends Controller
{
    public function __invoke(PropuestaCiudadana $propuesta)
    {
        abort_unless(auth()->user()?->can('propuestas.ver'), 403);

        $foto = $propuesta->getFirstMedia('foto');
        abort_unless($foto && is_readable($foto->getPath()), 404);

        return response()->file($foto->getPath(), [
            'Content-Type' => $foto->mime_type,
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
