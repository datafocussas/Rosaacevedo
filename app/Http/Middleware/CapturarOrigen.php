<?php

namespace App\Http\Middleware;

use App\Support\Origen;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Guarda en la sesión el origen del primer contacto (utm_* y referrer externo) para los formularios sin JavaScript. */
class CapturarOrigen
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('GET') && $request->hasSession()) {
            $origen = (array) $request->session()->get('origen', []);
            $cambio = false;

            foreach (Origen::CAMPOS_UTM as $campo) {
                if (($valor = $request->query($campo)) && is_string($valor) && empty($origen[$campo])) {
                    $origen[$campo] = mb_substr(strip_tags($valor), 0, 120);
                    $cambio = true;
                }
            }

            $referer = (string) $request->headers->get('referer');
            if ($referer && empty($origen['referrer']) && parse_url($referer, PHP_URL_HOST) !== $request->getHost()) {
                $origen['referrer'] = mb_substr($referer, 0, 255);
                $cambio = true;
            }

            if ($cambio) {
                $request->session()->put('origen', $origen);
            }
        }

        return $next($request);
    }
}
