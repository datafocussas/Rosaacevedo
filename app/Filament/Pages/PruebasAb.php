<?php

namespace App\Filament\Pages;

use App\Models\Banner;
use App\Models\Interaccion;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

/** Reporte A/B (RF-08): visitantes, registros y tasa de conversión por variante; declarar ganadora. */
class PruebasAb extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-beaker';

    protected static ?string $navigationGroup = 'Ciudadanía';

    protected static ?string $title = 'Pruebas A/B';

    protected static ?string $slug = 'pruebas-ab';

    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.pages.pruebas-ab';

    public ?string $desde = null;

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->can('ab.ver');
    }

    public function mount(): void
    {
        $this->desde = now()->subDays(30)->toDateString();
    }

    public function getFilas(): array
    {
        $base = Interaccion::query()->whereNotNull('variante')->where('created_at', '>=', $this->desde);

        $visitantes = (clone $base)->where('tipo', 'ab_visita')->select('variante', DB::raw('count(distinct visitante_id) as total'))->groupBy('variante')->pluck('total', 'variante');
        $registros = (clone $base)->where('tipo', 'registro_paso_1')->select('variante', DB::raw('count(distinct ciudadano_id) as total'))->groupBy('variante')->pluck('total', 'variante');
        $paso2 = (clone $base)->where('tipo', 'registro_paso_2')->select('variante', DB::raw('count(distinct ciudadano_id) as total'))->groupBy('variante')->pluck('total', 'variante');
        $whatsapp = (clone $base)->where('tipo', 'whatsapp_clic')->select('variante', DB::raw('count(*) as total'))->groupBy('variante')->pluck('total', 'variante');

        $activas = Banner::query()->vigentes()->where('pagina', 'inicio')->whereNotNull('variante')->get()->groupBy('variante');

        return $visitantes->keys()->merge($registros->keys())->merge($activas->keys())->unique()->sort()->map(fn ($v) => [
            'variante' => $v,
            'banners' => ($activas[$v] ?? collect())->pluck('titular')->join(' · ') ?: '(sin banners activos)',
            'visitantes' => (int) ($visitantes[$v] ?? 0),
            'registros' => (int) ($registros[$v] ?? 0),
            'paso2' => (int) ($paso2[$v] ?? 0),
            'whatsapp' => (int) ($whatsapp[$v] ?? 0),
            'tasa' => ($visitantes[$v] ?? 0) > 0 ? round(100 * ($registros[$v] ?? 0) / $visitantes[$v], 2) : null,
        ])->values()->all();
    }

    /** La ganadora queda sin variante (para todos); las demás variantes se archivan. */
    public function declararGanadora(string $variante): void
    {
        abort_unless(auth()->user()->can('contenido.gestionar'), 403);

        DB::transaction(function () use ($variante) {
            Banner::query()->where('pagina', 'inicio')->whereNotNull('variante')->where('variante', '!=', $variante)->where('estado', 'activo')
                ->get()->each->update(['estado' => 'archivado']);
            Banner::query()->where('pagina', 'inicio')->where('variante', $variante)->get()->each->update(['variante' => null]);
        });

        activity('ab')->causedBy(auth()->user())->withProperties(['ganadora' => $variante, 'reporte' => $this->getFilas()])->log('Declaró ganadora la variante '.$variante);
        Notification::make()->title('Variante '.$variante.' declarada ganadora')->success()->send();
    }
}
