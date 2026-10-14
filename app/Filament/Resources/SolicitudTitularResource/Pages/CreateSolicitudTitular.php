<?php

namespace App\Filament\Resources\SolicitudTitularResource\Pages;

use App\Filament\Resources\SolicitudTitularResource;
use App\Models\SolicitudTitular;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

/** Solicitudes que llegan por otros canales (teléfono, correo, en persona). */
class CreateSolicitudTitular extends CreateRecord
{
    protected static string $resource = SolicitudTitularResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['radicado'] = DB::transaction(fn () => SolicitudTitular::siguienteRadicado());
        $data['vence_en'] = SolicitudTitular::calcularVencimiento($data['tipo'])->toDateString();

        return $data;
    }
}
