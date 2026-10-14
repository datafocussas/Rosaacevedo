<?php

namespace Tests\Unit;

use App\Support\Celular;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CelularTest extends TestCase
{
    public static function casos(): array
    {
        return [
            ['3001234567', '+573001234567'],
            ['300 123 4567', '+573001234567'],
            ['+57 300-123-4567', '+573001234567'],
            ['573001234567', '+573001234567'],
            ['00573001234567', '+573001234567'],
            ['6041234567', null],   // fijo de Medellín
            ['300123456', null],    // 9 dígitos
            ['', null],
        ];
    }

    #[DataProvider('casos')]
    public function test_normaliza_a_e164(string $entrada, ?string $esperado): void
    {
        $this->assertSame($esperado, Celular::normalizar($entrada));
    }

    public function test_enmascara(): void
    {
        $this->assertSame('+57 300 *** **67', Celular::enmascarar('+573001234567'));
    }
}
