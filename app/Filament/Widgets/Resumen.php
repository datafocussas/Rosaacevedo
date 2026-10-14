<?php

namespace App\Filament\Widgets;

use App\Models\Ciudadano;
use App\Models\CrmOutbox;
use App\Models\PropuestaCiudadana;
use App\Models\SolicitudTitular;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/** Resumen de captación y operación. Solo cifras agregadas, sin datos personales. */
class Resumen extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $pendientes = CrmOutbox::query()->whereIn('estado', ['pendiente', 'error'])->count();
        $errores = CrmOutbox::query()->where('estado', 'error')->where('intentos', '>=', 5)->count();

        return [
            Stat::make('Registros', number_format(Ciudadano::query()->count(), 0, ',', '.'))
                ->description(Ciudadano::query()->where('created_at', '>=', now()->startOfDay())->count().' hoy'),
            Stat::make('Propuestas por revisar', PropuestaCiudadana::query()->where('estado', 'recibida')->count()),
            Stat::make('Solicitudes del titular por vencer', SolicitudTitular::query()->whereIn('estado', ['abierta', 'en_tramite'])->where('vence_en', '<=', now()->addDays(5))->count())
                ->color('danger'),
            Stat::make('Pendientes de enviar al CRM', $pendientes)
                ->description($errores ? $errores.' con 5 o más fallos' : (config('rosa.crm.driver') === 'nulo' ? 'CRM sin configurar: se guardan y se enviarán al conectarlo' : 'Sincronización al día'))
                ->color($errores ? 'danger' : 'gray'),
        ];
    }
}
