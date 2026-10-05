<?php

namespace App\Http\Middleware;

use App\Http\Controllers\DosFactoresController;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExigirDosFactores
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if (! $usuario || ! config('rosa.dos_factores', true)) {
            return $next($request);
        }

        if ($request->session()->get(DosFactoresController::SESION) !== $usuario->id) {
            if (! $request->expectsJson()) {
                $request->session()->put('url.intended', $request->fullUrl());
            }

            return redirect()->route('dos-factores.mostrar');
        }

        return $next($request);
    }
}
