<?php

namespace App\Support;

final class RespuestaCrm
{
    public function __construct(
        public readonly bool $ok,
        public readonly ?string $crmId = null,
        public readonly ?string $error = null,
        public readonly bool $reintentar = true,
    ) {}

    public static function exito(?string $crmId = null): self
    {
        return new self(true, $crmId);
    }

    public static function fallo(string $error, bool $reintentar = true): self
    {
        return new self(false, null, $error, $reintentar);
    }
}
