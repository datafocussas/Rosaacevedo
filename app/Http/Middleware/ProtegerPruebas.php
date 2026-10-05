<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** pruebas.rosaacevedo.com lleva contraseña (sección 06). Solo actúa si PRUEBAS_USUARIO y PRUEBAS_CLAVE existen. */
class ProtegerPruebas
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = config('rosa.pruebas.usuario');
        $clave = config('rosa.pruebas.clave');

        if (! $usuario || ! $clave || $request->is('up', 'api/*')) {
            return $next($request);
        }

        if (hash_equals($usuario, (string) $request->getUser()) && hash_equals($clave, (string) $request->getPassword())) {
            return $next($request);
        }

        return response('Entorno de pruebas.', 401, ['WWW-Authenticate' => 'Basic realm="Pruebas Rosa Acevedo"']);
    }
}
