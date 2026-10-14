<?php

namespace App\Services;

use App\Models\Politica;
use Illuminate\Support\Facades\Cache;

/** Versiones vigentes de los textos legales, con caché (se limpia al activar una versión). */
class TextosLegales
{
    private array $memoria = [];

    public function vigente(string $tipo): ?Politica
    {
        if (array_key_exists($tipo, $this->memoria)) {
            return $this->memoria[$tipo];
        }

        try {
            return $this->memoria[$tipo] = Cache::remember('politica-vigente:'.$tipo, 3600, fn () => Politica::vigente($tipo));
        } catch (\Throwable) {
            return $this->memoria[$tipo] = null;
        }
    }

    public function olvidar(): void
    {
        $this->memoria = [];
        foreach (array_keys(Politica::TIPOS) as $tipo) {
            Cache::forget('politica-vigente:'.$tipo);
        }
    }
}
