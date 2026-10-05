<?php

namespace App\Support;

use RuntimeException;

/**
 * Celular colombiano en E.164 (+57 y 10 dígitos que inician en 3), su HMAC para deduplicar
 * y su versión enmascarada para el panel (RF-02).
 */
final class Celular
{
    public static function normalizar(?string $valor): ?string
    {
        $digitos = preg_replace('/\D+/', '', (string) $valor);

        if (strlen($digitos) === 12 && str_starts_with($digitos, '57')) {
            $digitos = substr($digitos, 2);
        } elseif (strlen($digitos) === 14 && str_starts_with($digitos, '0057')) {
            $digitos = substr($digitos, 4);
        }

        return preg_match('/^3\d{9}$/', $digitos) ? '+57'.$digitos : null;
    }

    public static function hmac(string $e164): string
    {
        $clave = (string) config('rosa.celular_hmac_key');

        if ($clave === '') {
            throw new RuntimeException('Falta CELULAR_HMAC_KEY en el .env.');
        }

        return hash_hmac('sha256', $e164, $clave);
    }

    /** +57 300 *** **67 */
    public static function enmascarar(string $e164): string
    {
        $local = substr($e164, -10);

        return '+57 '.substr($local, 0, 3).' *** **'.substr($local, -2);
    }

    /** 300 123 4567 */
    public static function formatear(string $e164): string
    {
        $local = substr($e164, -10);

        return substr($local, 0, 3).' '.substr($local, 3, 3).' '.substr($local, 6);
    }
}
