<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Token firmado de un solo uso por paso, con vigencia de 7 días (sección 05).
 * Nunca contiene el id del ciudadano: lleva su uuid, el paso que habilita y un identificador único.
 */
final class TokenRegistro
{
    public static function emitir(string $uuid, int $paso): string
    {
        $datos = [
            'c' => $uuid,
            'p' => $paso,
            'e' => now()->addDays(config('rosa.token_registro_dias', 7))->timestamp,
            'j' => Str::random(16),
        ];

        $cuerpo = self::b64(json_encode($datos));

        return $cuerpo.'.'.self::firma($cuerpo);
    }

    /**
     * Valida y consume el token. Devuelve ['uuid' => …, 'paso' => …] o null.
     */
    public static function consumir(string $token): ?array
    {
        $datos = self::leer($token);

        if (! $datos) {
            return null;
        }

        $segundos = max(60, $datos['e'] - now()->timestamp);
        if (! Cache::add('token-registro:'.$datos['j'], 1, $segundos)) {
            return null; // Ya se usó.
        }

        return ['uuid' => $datos['c'], 'paso' => (int) $datos['p']];
    }

    /** Datos de un token válido que aún no se ha usado, sin consumirlo. */
    public static function disponible(string $token): ?array
    {
        $datos = self::leer($token);

        return $datos && ! Cache::has('token-registro:'.$datos['j']) ? $datos : null;
    }

    public static function leer(string $token): ?array
    {
        [$cuerpo, $firma] = array_pad(explode('.', $token, 2), 2, '');

        if ($cuerpo === '' || ! hash_equals(self::firma($cuerpo), $firma)) {
            return null;
        }

        $datos = json_decode(base64_decode(strtr($cuerpo, '-_', '+/')), true);

        if (! is_array($datos) || ! isset($datos['c'], $datos['p'], $datos['e'], $datos['j']) || $datos['e'] < now()->timestamp) {
            return null;
        }

        return $datos;
    }

    private static function firma(string $cuerpo): string
    {
        return self::b64(hash_hmac('sha256', 'registro|'.$cuerpo, (string) config('app.key'), true));
    }

    private static function b64(string $valor): string
    {
        return rtrim(strtr(base64_encode($valor), '+/', '-_'), '=');
    }
}
