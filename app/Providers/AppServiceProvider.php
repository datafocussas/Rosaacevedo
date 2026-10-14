<?php

namespace App\Providers;

use App\Contracts\CrmCliente;
use App\Models\MenuItem;
use App\Models\RedSocial;
use App\Services\Ajustes;
use App\Services\Crm\CrmConexion;
use App\Services\Crm\HttpCrmCliente;
use App\Services\Crm\NuloCrmCliente;
use App\Services\TextosLegales;
use Carbon\Carbon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Ajustes::class);
        $this->app->singleton(TextosLegales::class);

        // El sitio solo conoce la interfaz: cuando llegue el contrato del CRM se cambia el adaptador.
        // La conexión se configura en el panel (Sitio → Conexión con el CRM) o, en su defecto, en el .env.
        $this->app->bind(CrmCliente::class, fn () => CrmConexion::habilitada() ? new HttpCrmCliente : new NuloCrmCliente);
    }

    public function boot(): void
    {
        Carbon::setLocale('es');
        setlocale(LC_TIME, 'es_CO.UTF-8', 'es_ES.UTF-8', 'es');

        // RF-05: 5 envíos por IP cada 10 minutos.
        RateLimiter::for('envios', fn (Request $request) => Limit::perMinutes(
            config('rosa.limites.por_ip_minutos', 10),
            config('rosa.limites.por_ip', 5),
        )->by($request->ip())->response(function (Request $request, array $headers) {
            $mensaje = 'Recibimos muchos envíos desde tu conexión. Espera unos minutos e inténtalo de nuevo.';

            return $request->expectsJson()
                ? response()->json(['message' => $mensaje], 429, $headers)
                : back()->withInput()->withErrors(['turnstile' => $mensaje]);
        }));

        // El Administrador puede todo en el panel.
        Gate::before(fn ($usuario) => method_exists($usuario, 'esAdministrador') && $usuario->esAdministrador() ? true : null);

        View::composer('components.layouts.sitio', function ($view) {
            $datos = Cache::remember('layout-sitio', 600, function () {
                try {
                    $menu = MenuItem::query()->where('activo', true)->orderBy('orden')->get()->groupBy('ubicacion');

                    return [
                        'menuPrincipal' => ($menu['principal'] ?? collect())->take(6)->values(),
                        'menuPieSitio' => ($menu['pie_sitio'] ?? collect())->values(),
                        'menuPieTransparencia' => ($menu['pie_transparencia'] ?? collect())->values(),
                        'redes' => RedSocial::query()->where('activa', true)->orderBy('orden')->get(),
                    ];
                } catch (\Throwable) {
                    return ['menuPrincipal' => collect(), 'menuPieSitio' => collect(), 'menuPieTransparencia' => collect(), 'redes' => collect()];
                }
            });

            $view->with($datos);
        });
    }
}
