<?php

namespace Tests\Unit;

use App\Support\Fondos;
use PHPUnit\Framework\TestCase;

class FondosTest extends TestCase
{
    private function bloque(string $tipo, ?string $fondo = null): array
    {
        return ['type' => $tipo, 'data' => array_filter(['activo' => true, 'fondo' => $fondo])];
    }

    public function test_dos_oscuros_seguidos_el_segundo_pasa_a_claro_si_puede(): void
    {
        $r = Fondos::resolver([$this->bloque('raices'), $this->bloque('cita')]);

        $this->assertSame(['abismo', 'marfil'], array_column($r, 'fondo'));
    }

    public function test_dos_oscuros_sin_alternativa_clara_llevan_separador(): void
    {
        $r = Fondos::resolver([$this->bloque('buzon'), $this->bloque('redes')]);

        $this->assertSame(['esmeralda', 'noche'], array_column($r, 'fondo'));
        $this->assertFalse($r[0]['separador']);
        $this->assertTrue($r[1]['separador']);
    }

    public function test_dos_claros_iguales_seguidos_alternan(): void
    {
        $r = Fondos::resolver([$this->bloque('texto'), $this->bloque('texto'), $this->bloque('texto')]);

        $this->assertSame(['marfil', 'blanco', 'marfil'], array_column($r, 'fondo'));
    }

    public function test_un_fondo_no_permitido_vuelve_al_de_por_defecto(): void
    {
        $this->assertSame('noche', Fondos::elegido($this->bloque('registro', 'marfil')));
        $this->assertSame('abismo', Fondos::elegido($this->bloque('imagen_texto', 'raiz')));
    }

    public function test_proporcion_oscura(): void
    {
        $bloques = [$this->bloque('registro'), $this->bloque('texto'), $this->bloque('texto'), $this->bloque('raices')];

        $this->assertSame(0.5, Fondos::proporcionOscura($bloques));
    }
}
