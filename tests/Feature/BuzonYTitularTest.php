<?php

namespace Tests\Feature;

use App\Models\Ciudadano;
use App\Models\PropuestaCiudadana;
use App\Models\SolicitudTitular;
use App\Models\Tema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuzonYTitularTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_propuesta_recibe_codigo_y_reutiliza_al_ciudadano(): void
    {
        $this->registrar()->assertCreated();
        $tema = Tema::query()->first();

        $respuesta = $this->postJson('/api/v1/propuestas', [
            'tema_id' => $tema->id,
            'texto' => 'El puesto de salud de San Pío X necesita atender en la tarde.',
            'nombre' => 'Diana',
            'celular' => '3001234567',
            'consent' => ['general' => true, 'publicar' => true],
        ])->assertCreated();

        $this->assertMatchesRegularExpression('/^PR-\d{4}-0001$/', $respuesta->json('codigo'));
        $this->assertSame(1, Ciudadano::query()->count());
        $this->assertTrue(PropuestaCiudadana::query()->sole()->publicar_anonima);
    }

    public function test_la_solicitud_del_titular_queda_radicada_con_plazo(): void
    {
        $this->registrar()->assertCreated();

        $respuesta = $this->postJson('/api/v1/titular/solicitudes', [
            'tipo' => 'consulta', 'nombre' => 'Diana', 'contacto' => '300 123 4567',
        ])->assertCreated()->assertJsonStructure(['radicado', 'vence_en']);

        $solicitud = SolicitudTitular::query()->sole();
        $this->assertSame($respuesta->json('radicado'), $solicitud->radicado);
        $this->assertNotNull($solicitud->ciudadano_id);
        $this->assertSame('+57 300 *** **67', $solicitud->contacto);
        $this->assertTrue($solicitud->vence_en->isFuture());
    }
}
