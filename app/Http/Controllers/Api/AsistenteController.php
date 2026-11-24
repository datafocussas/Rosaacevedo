<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConversacionAsistente;
use App\Services\Asistente\Asistente;
use App\Services\Asistente\AsistenteConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/** POST /api/v1/asistente: una pregunta del chat del sitio y su respuesta. Sin datos de contacto. */
class AsistenteController extends Controller
{
    public function __invoke(Request $request, Asistente $asistente, AsistenteConfig $config)
    {
        abort_unless($config->disponible(), 404);

        $datos = $request->validate([
            'mensaje' => ['required', 'string', 'max:'.Asistente::MAX_CARACTERES],
            'conversacion' => ['nullable', 'uuid'],
            'pagina' => ['nullable', 'string', 'max:255'],
        ], [
            'mensaje.required' => 'Escribe tu pregunta.',
            'mensaje.max' => 'Tu pregunta es muy larga: usa máximo '.Asistente::MAX_CARACTERES.' caracteres.',
        ]);

        $conversacion = filled($datos['conversacion'] ?? null)
            ? ConversacionAsistente::query()->where('uuid', $datos['conversacion'])->first()
            : null;

        $conversacion ??= ConversacionAsistente::query()->create([
            'uuid' => (string) Str::uuid(),
            'ip_hash' => hash_hmac('sha256', (string) $request->ip(), (string) config('app.key')),
            'pagina' => Str::limit(parse_url((string) ($datos['pagina'] ?? ''), PHP_URL_PATH) ?: '/', 250, ''),
        ]);

        $respuesta = $asistente->responder($conversacion, $datos['mensaje']);

        return response()->json([
            'conversacion' => $conversacion->uuid,
            'respuesta' => $respuesta['texto'],
            'estado' => $respuesta['estado'],
        ])->header('Cache-Control', 'no-store');
    }
}
