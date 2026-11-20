<?php

namespace App\Services\Asistente;

final class RespuestaModelo
{
    public function __construct(
        public readonly string $texto,
        public readonly int $tokensEntrada = 0,
        public readonly int $tokensSalida = 0,
        public readonly bool $rechazada = false,
    ) {}
}
