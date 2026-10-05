<?php

use App\Http\Middleware\AsignarVariante;
use App\Http\Middleware\CabecerasSeguridad;
use App\Http\Middleware\CapturarOrigen;
use App\Http\Middleware\ProtegerPruebas;
use App\Models\Redireccion;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Csp\AddCspHeaders;
use Spatie\ResponseCache\Middlewares\CacheResponse;
use Spatie\ResponseCache\Middlewares\DoNotCacheResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api/v1',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Cloudflare delante de Hostinger: la IP real llega en X-Forwarded-For.
        $middleware->trustProxies(at: '*');
        $middleware->prepend(ProtegerPruebas::class);
        $middleware->append(CabecerasSeguridad::class);
        // Cookies técnicas que también lee JavaScript o la API (sin cifrar; no llevan datos personales).
        $middleware->encryptCookies(except: [AsignarVariante::COOKIE, 'ra_cookies']);
        $middleware->alias([
            'variante' => AsignarVariante::class,
            'origen' => CapturarOrigen::class,
            'csp' => AddCspHeaders::class,
            'cache.respuesta' => CacheResponse::class,
            'sin.cache' => DoNotCacheResponse::class,
        ]);
        $middleware->redirectGuestsTo(fn () => route('filament.admin.auth.login'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Redirecciones 301 administrables (RF-45): solo se consultan cuando la ruta no existe.
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if (! $request->isMethod('GET')) {
                return null;
            }

            try {
                $redireccion = Redireccion::query()->where('desde', Redireccion::normalizar($request->getPathInfo()))->first();
            } catch (Throwable) {
                return null;
            }

            if ($redireccion) {
                $redireccion->increment('visitas');

                return redirect($redireccion->hacia, in_array($redireccion->codigo, [301, 302, 307, 308], true) ? $redireccion->codigo : 301);
            }

            return null;
        });
    })->create();
