<?php

namespace Tests\Feature;

use App\Models\Ciudadano;
use App\Models\Consentimiento;
use App\Models\CrmOutbox;
use App\Models\Interaccion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use LogicException;
use Tests\TestCase;

class RegistroTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_paso_1_crea_el_ciudadano_sin_guardar_el_celular_en_claro(): void
    {
        $this->registrar(['consent' => ['whatsapp' => true]])
            ->assertCreated()
            ->assertJsonStructure(['token', 'codigo', 'paso']);

        $ciudadano = Ciudadano::query()->sole();
        $this->assertSame('+573001234567', $ciudadano->celular());
        $fila = DB::table('ciudadanos')->first();
        $this->assertStringNotContainsString('3001234567', (string) $fila->celular_cifrado);
        $this->assertSame(64, strlen($fila->celular_hash));
        $this->assertSame('facebook', $ciudadano->primer_origen['utm_source']);
    }

    /** Criterio de aceptación 2: el mismo celular dos veces → un ciudadano y dos interacciones. */
    public function test_el_mismo_celular_produce_un_solo_ciudadano_y_dos_interacciones(): void
    {
        $this->registrar()->assertCreated();
        $this->registrar(['celular' => '+57 300-123-4567', 'nombre' => 'Diana María'])->assertCreated();

        $this->assertSame(1, Ciudadano::query()->count());
        $this->assertSame(2, Interaccion::query()->where('tipo', 'registro_paso_1')->count());
        $this->assertSame('Diana', Ciudadano::query()->sole()->nombre);
    }

    /** Criterio 3: consentimientos con versión, IP y hora; ninguna casilla marcada por defecto. */
    public function test_los_consentimientos_guardan_evidencia_por_casilla(): void
    {
        $this->registrar(['consent' => ['whatsapp' => true]])->assertCreated();

        $consentimientos = Consentimiento::query()->with('politica')->get();
        $this->assertEqualsCanonicalizing(['autorizacion_general', 'whatsapp'], $consentimientos->pluck('politica.tipo')->all());
        foreach ($consentimientos as $c) {
            $this->assertTrue($c->otorgado);
            $this->assertSame('registro_p1', $c->formulario);
            $this->assertSame('127.0.0.1', $c->ipLegible());
            $this->assertSame(64, strlen($c->politica->hash_sha256));
            $this->assertNotNull($c->created_at);
        }
    }

    public function test_sin_whatsapp_no_hay_consentimiento_de_whatsapp(): void
    {
        $this->registrar()->assertCreated();

        $this->assertSame(['autorizacion_general'], Consentimiento::query()->with('politica')->get()->pluck('politica.tipo')->all());
    }

    public function test_la_autorizacion_general_es_obligatoria(): void
    {
        $this->registrar(['consent' => ['general' => false]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['consent.general']);

        $this->assertSame(0, Ciudadano::query()->count());
    }

    public function test_valida_el_celular_colombiano(): void
    {
        $this->registrar(['celular' => '604 123 4567'])->assertUnprocessable()->assertJsonValidationErrors(['celular']);
    }

    public function test_las_casillas_nunca_estan_premarcadas(): void
    {
        foreach (['/', '/sumate', '/buzon'] as $ruta) {
            $html = $this->get($ruta)->assertOk()->getContent();
            preg_match_all('/<input[^>]*type="checkbox"[^>]*>/', $html, $casillas);
            $this->assertNotEmpty($casillas[0], $ruta);
            foreach ($casillas[0] as $casilla) {
                if (str_contains($casilla, 'disabled')) {
                    continue; // «Cookies técnicas (necesarias)»: informativa, no es una autorización.
                }
                $this->assertDoesNotMatchRegularExpression('/\schecked\b/', $casilla, "Casilla premarcada en {$ruta}");
            }
        }
    }

    public function test_la_tabla_consentimientos_es_de_solo_insercion(): void
    {
        $this->registrar()->assertCreated();
        $consentimiento = Consentimiento::query()->first();

        $this->expectException(LogicException::class);
        $consentimiento->update(['otorgado' => false]);
    }

    public function test_el_campo_trampa_bloquea_el_envio(): void
    {
        $this->registrar(['sitio_web' => 'http://spam.example'])->assertUnprocessable();
        $this->assertSame(0, Ciudadano::query()->count());
    }

    public function test_los_pasos_2_y_3_completan_el_registro_con_un_token_de_un_solo_uso(): void
    {
        $barrio = $this->barrio();
        $token = $this->registrar()->json('token');

        $paso2 = $this->patchJson('/api/v1/registro/'.$token, ['email' => 'diana@ejemplo.co', 'barrio_id' => $barrio->id])
            ->assertOk()->assertJson(['paso' => 2]);

        // El mismo token no se puede reutilizar.
        $this->patchJson('/api/v1/registro/'.$token, ['email' => 'otro@ejemplo.co'])->assertUnprocessable()->assertJsonValidationErrors(['token']);

        $this->patchJson('/api/v1/registro/'.$paso2->json('token'), [
            'intereses' => ['vecinos', 'redes'],
            'puesto_votacion' => 'I. E. Simón Bolívar',
            'consent' => ['afinidad' => true],
        ])->assertOk()->assertJson(['paso' => 3, 'token' => null]);

        $ciudadano = Ciudadano::query()->sole();
        $this->assertSame('diana@ejemplo.co', $ciudadano->email);
        $this->assertSame($barrio->id, $ciudadano->barrio_id);
        $this->assertSame(3, $ciudadano->paso_alcanzado);
        $this->assertTrue($ciudadano->es_voluntario);
        $this->assertSame('I. E. Simón Bolívar', $ciudadano->voluntariado->puesto_votacion);
        $this->assertArrayHasKey('afinidad_politica', $ciudadano->consentimientosVigentes());
    }

    public function test_un_token_alterado_no_sirve(): void
    {
        $token = $this->registrar()->json('token');

        $this->patchJson('/api/v1/registro/'.$token.'x', ['email' => 'a@b.co'])->assertUnprocessable()->assertJsonValidationErrors(['token']);
    }

    public function test_el_formulario_html_funciona_sin_javascript(): void
    {
        $this->post('/sumate', [
            'nombre' => 'Carlos',
            'celular' => '3109876543',
            'consent' => ['general' => '1'],
        ])->assertRedirect('/sumate/continuar');

        $this->get('/sumate/continuar')->assertOk()->assertSee('Cuéntanos de tu barrio');
        $this->post('/sumate/continuar', ['email' => 'carlos@ejemplo.co'])->assertRedirect('/sumate/continuar');
        $this->assertSame(2, Ciudadano::query()->sole()->paso_alcanzado);
    }

    public function test_cada_envio_escribe_en_el_outbox_del_crm(): void
    {
        $this->registrar()->assertCreated();

        $fila = CrmOutbox::query()->where('entidad', 'ciudadano')->sole();
        $this->assertSame('pendiente', $fila->estado);
        $this->assertSame('+573001234567', $fila->payload['celular']);
        $this->assertSame('autorizacion_general', $fila->payload['consentimientos'][0]['tipo']);
    }

    public function test_limite_de_tres_envios_por_celular_al_dia(): void
    {
        $this->registrar()->assertCreated();
        $this->registrar()->assertCreated();
        $this->registrar()->assertCreated();
        $this->registrar()->assertUnprocessable()->assertJsonValidationErrors(['celular']);
    }
}
