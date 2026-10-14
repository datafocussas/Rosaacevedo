<?php

namespace Tests\Unit;

use App\Support\DiasHabiles;
use Carbon\Carbon;
use Tests\TestCase;

class DiasHabilesTest extends TestCase
{
    protected bool $seed = false;

    public function test_festivos_de_colombia_2026(): void
    {
        $festivos = DiasHabiles::festivos(2026);

        $this->assertContains('2026-04-02', $festivos); // Jueves santo
        $this->assertContains('2026-04-03', $festivos); // Viernes santo
        $this->assertContains('2026-10-12', $festivos); // Día de la Raza (lunes)
        $this->assertContains('2026-11-02', $festivos); // Todos los Santos trasladado
        $this->assertContains('2026-11-16', $festivos); // Independencia de Cartagena trasladado
        $this->assertContains('2026-12-08', $festivos);
    }

    public function test_suma_dias_habiles_saltando_fines_de_semana_y_festivos(): void
    {
        // Viernes 9 de octubre de 2026 + 10 días hábiles: salta el festivo del 12 de octubre.
        $this->assertSame('2026-10-26', DiasHabiles::sumar(Carbon::parse('2026-10-09'), 10)->toDateString());
    }
}
