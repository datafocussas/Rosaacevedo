<?php

namespace App\Services;

use App\Models\MenuItem;
use App\Models\Pagina;

/**
 * Enlaces de una página en el menú principal y en el pie, administrados desde el formulario de la página.
 * El ítem del menú se identifica por la dirección de la página («/mi-pagina»).
 */
class MenuPaginas
{
    public const UBICACIONES = [
        'principal' => 'Menú principal',
        'pie_sitio' => 'Pie: «El sitio»',
        'pie_transparencia' => 'Pie: «Transparencia»',
    ];

    public const MAX_PRINCIPAL = 6;

    /** Ubicaciones donde ya aparece la página. */
    public function ubicaciones(Pagina $pagina): array
    {
        return MenuItem::query()->where('url', $pagina->ruta())->pluck('ubicacion')->unique()->values()->all();
    }

    public function texto(Pagina $pagina): ?string
    {
        return MenuItem::query()->where('url', $pagina->ruta())->value('texto');
    }

    /** ¿Cabe en el menú principal (máximo 6 ítems activos)? */
    public function cabeEnPrincipal(?string $ruta): bool
    {
        return MenuItem::query()->where('ubicacion', 'principal')->where('activo', true)
            ->when($ruta, fn ($q) => $q->where('url', '!=', $ruta))->count() < self::MAX_PRINCIPAL;
    }

    public function sincronizar(Pagina $pagina, array $ubicaciones, ?string $texto, ?string $rutaAnterior = null): void
    {
        $ruta = $pagina->ruta();
        $texto = trim((string) $texto) ?: $pagina->titulo;

        if ($rutaAnterior && $rutaAnterior !== $ruta) {
            MenuItem::query()->where('url', $rutaAnterior)->update(['url' => $ruta]);
        }

        foreach (array_keys(self::UBICACIONES) as $ubicacion) {
            $item = MenuItem::query()->where('ubicacion', $ubicacion)->where('url', $ruta)->first();

            if (in_array($ubicacion, $ubicaciones, true)) {
                if ($item) {
                    $item->update(['texto' => mb_substr($texto, 0, 60), 'activo' => true]);
                } else {
                    MenuItem::query()->create([
                        'ubicacion' => $ubicacion,
                        'texto' => mb_substr($texto, 0, 60),
                        'url' => $ruta,
                        'orden' => (int) MenuItem::query()->where('ubicacion', $ubicacion)->max('orden') + 1,
                        'activo' => true,
                    ]);
                }
            } elseif ($item) {
                $item->delete();
            }
        }
    }
}
