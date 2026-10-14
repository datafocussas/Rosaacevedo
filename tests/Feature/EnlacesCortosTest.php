<?php

namespace Tests\Feature;

use App\Models\Ciudadano;
use App\Models\EnlaceCorto;
use App\Models\Interaccion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnlacesCortosTest extends TestCase
{
    use RefreshDatabase;

    /** Criterio 7: un /q/ impreso en un volante atribuye los registros a esa pieza. */
    public function test_el_qr_redirige_cuenta_el_clic_y_atribuye_el_registro(): void
    {
        $enlace = EnlaceCorto::query()->create(['codigo' => 'VOL-C4', 'destino' => '/sumate', 'pieza' => 'Volante C4']);

        $this->get('/q/VOL-C4')->assertRedirect('/sumate?utm_source=qr&utm_medium=impreso&utm_campaign=VOL-C4&utm_content=Volante+C4');
        $this->assertSame(1, $enlace->fresh()->clics);

        $this->post('/sumate', ['nombre' => 'Luz', 'celular' => '3015550000', 'consent' => ['general' => '1']])->assertRedirect();

        $this->assertSame('VOL-C4', Interaccion::query()->where('tipo', 'registro_paso_1')->value('codigo_q'));
        $this->assertSame('VOL-C4', Ciudadano::query()->sole()->primer_origen['codigo_q']);
    }

    public function test_un_codigo_inexistente_lleva_al_inicio(): void
    {
        $this->get('/q/NO-EXISTE')->assertRedirect('/');
    }
}
