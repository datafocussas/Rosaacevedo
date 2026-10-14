<?php

namespace App\Support;

use App\Http\Middleware\AsignarVariante;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Origen de un envío (RF-03): utm_*, código /q/, página, comuna de la página, evento,
 * variante A/B y referrer. Combina lo que manda el formulario con lo que se guardó en la
 * sesión al llegar al sitio.
 */
final class Origen
{
    public const CAMPOS_UTM = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content'];

    public function __construct(
        public readonly ?string $utm_source = null,
        public readonly ?string $utm_medium = null,
        public readonly ?string $utm_campaign = null,
        public readonly ?string $utm_content = null,
        public readonly ?string $codigo_q = null,
        public readonly ?string $pagina = null,
        public readonly ?int $comuna_pagina_id = null,
        public readonly ?int $evento_id = null,
        public readonly ?string $variante = null,
        public readonly ?string $referrer = null,
        public readonly ?string $visitante_id = null,
    ) {}

    public static function desdePeticion(Request $request): self
    {
        $formulario = (array) $request->input('origen', []);
        $sesion = $request->hasSession() ? (array) $request->session()->get('origen', []) : [];
        $valor = fn (string $campo, int $max) => self::limpiar($formulario[$campo] ?? $sesion[$campo] ?? null, $max);

        $comuna = $formulario['comuna_pagina'] ?? $sesion['comuna_pagina'] ?? null;

        return new self(
            utm_source: $valor('utm_source', 80),
            utm_medium: $valor('utm_medium', 80),
            utm_campaign: $valor('utm_campaign', 120),
            utm_content: $valor('utm_content', 120),
            codigo_q: $valor('codigo_q', 20),
            pagina: $valor('pagina', 255) ?? self::limpiar(parse_url((string) $request->headers->get('referer'), PHP_URL_PATH), 255),
            comuna_pagina_id: is_numeric($comuna) ? (int) $comuna : null,
            evento_id: is_numeric($formulario['evento'] ?? null) ? (int) $formulario['evento'] : ($sesion['evento_id'] ?? null),
            variante: self::limpiar($request->attributes->get('variante') ?? $formulario['variante'] ?? null, 10),
            referrer: $valor('referrer', 255),
            visitante_id: self::limpiar($request->cookie(AsignarVariante::COOKIE), 36),
        );
    }

    public function paraInteraccion(): array
    {
        return [
            'visitante_id' => $this->visitante_id,
            'variante' => $this->variante,
            'pagina' => $this->pagina,
            'comuna_pagina_id' => $this->comuna_pagina_id,
            'utm_source' => $this->utm_source,
            'utm_medium' => $this->utm_medium,
            'utm_campaign' => $this->utm_campaign,
            'utm_content' => $this->utm_content,
            'codigo_q' => $this->codigo_q,
            'referrer' => $this->referrer,
        ];
    }

    public function paraCiudadano(): array
    {
        return array_filter([
            'utm_source' => $this->utm_source,
            'utm_medium' => $this->utm_medium,
            'utm_campaign' => $this->utm_campaign,
            'utm_content' => $this->utm_content,
            'codigo_q' => $this->codigo_q,
            'pagina' => $this->pagina,
            'comuna_pagina_id' => $this->comuna_pagina_id,
            'evento_id' => $this->evento_id,
            'variante' => $this->variante,
            'referrer' => $this->referrer,
        ], fn ($v) => $v !== null);
    }

    private static function limpiar(mixed $valor, int $max): ?string
    {
        if (! is_scalar($valor)) {
            return null;
        }

        $valor = trim(strip_tags((string) $valor));

        return $valor === '' ? null : Str::limit($valor, $max, '');
    }
}
