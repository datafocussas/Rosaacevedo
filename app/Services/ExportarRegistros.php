<?php

namespace App\Services;

use App\Models\Ciudadano;
use App\Models\User;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Exportación de registros (RF-43): filtrada, con motivo obligatorio, marca de agua en el archivo
 * (encabezado y columna con el identificador de la exportación) y registro en la bitácora.
 * Nunca exporta a quien revocó su autorización.
 */
class ExportarRegistros
{
    public function descargar(User $usuario, array $filtros): StreamedResponse
    {
        abort_unless($usuario->can('registros.exportar'), 403);

        $id = 'EXP-'.now()->format('Ymd-His').'-'.Str::upper(Str::random(4));
        $consulta = $this->consulta($filtros);
        $total = (clone $consulta)->count();

        activity('exportaciones')->causedBy($usuario)->withProperties([
            'exportacion' => $id,
            'motivo' => $filtros['motivo'],
            'filtros' => collect($filtros)->except('motivo')->filter()->all(),
            'registros' => $total,
            'ip' => request()->ip(),
        ])->log('Exportó registros');

        return response()->streamDownload(function () use ($consulta, $usuario, $filtros, $id) {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF");
            fputcsv($salida, ["CONFIDENCIAL · Uso interno de la campaña · {$id} · Exportado por {$usuario->email} el ".now()->format('Y-m-d H:i').' · Motivo: '.str_replace(["\n", "\r"], ' ', $filtros['motivo'])]);
            fputcsv($salida, ['nombre', 'celular', 'email', 'barrio', 'comuna_2024', 'comuna_2007', 'paso', 'voluntario', 'whatsapp', 'registrado', 'marca']);

            $consulta->with('barrio.comuna', 'barrio.comuna2007')->chunkById(500, function ($ciudadanos) use ($salida, $id) {
                foreach ($ciudadanos as $c) {
                    $vigentes = $c->consentimientosVigentes();
                    fputcsv($salida, [
                        $c->nombre, $c->celular(), $c->email, $c->barrio?->nombre, $c->barrio?->comuna?->codigo, $c->barrio?->comuna2007?->codigo,
                        $c->paso_alcanzado, $c->es_voluntario ? 'sí' : 'no', ($vigentes['whatsapp'] ?? false) ? 'sí' : 'no',
                        $c->created_at->format('Y-m-d'), $id,
                    ]);
                }
            });

            fclose($salida);
        }, $id.'.csv', ['Content-Type' => 'text/csv; charset=utf-8']);
    }

    public function consulta(array $f)
    {
        return Ciudadano::query()->where('estado', '!=', 'retirado')
            ->when($f['comuna_id'] ?? null, fn ($q, $comuna) => $q->whereHas('barrio', fn ($b) => $b->where('comuna_2024_id', $comuna)))
            ->when($f['paso'] ?? null, fn ($q, $paso) => $q->where('paso_alcanzado', '>=', $paso))
            ->when($f['desde'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($f['hasta'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->when($f['solo_whatsapp'] ?? false, fn ($q) => $q->whereRaw(
                "(select c.otorgado from consentimientos c join politicas p on p.id = c.politica_id where c.ciudadano_id = ciudadanos.id and p.tipo = 'whatsapp' order by c.created_at desc, c.id desc limit 1) = 1"
            ));
    }
}
