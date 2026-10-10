<?php

namespace Tests\Feature;

use App\Contracts\CrmCliente;
use App\Jobs\SincronizarCrm;
use App\Models\Ciudadano;
use App\Models\CrmOutbox;
use App\Models\TerritorioComuna;
use App\Models\User;
use App\Services\Ajustes;
use App\Services\Crm\CrmConexion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

/** Conexión con el CRM configurada desde el panel: URL, API key cifrada, campos y envío automático. */
class ConexionCrmPanelTest extends TestCase
{
    use RefreshDatabase;

    private function configurar(array $campos = []): void
    {
        config(['rosa.crm.driver' => 'nulo']);
        app(Ajustes::class)->set(CrmConexion::CLAVE, [
            'activo' => true,
            'url_ciudadano' => 'https://crm.campana.test/api/leads',
            'url_propuesta' => null,
            'autenticacion' => 'bearer',
            'encabezado' => null,
            'campos_ciudadano' => $campos,
            'campos_propuesta' => [],
        ], 'crm');
        app(CrmConexion::class)->guardarApiKey('llave-secreta-123');
    }

    private function sincronizar(): void
    {
        app(SincronizarCrm::class)->handle(app(CrmCliente::class));
    }

    public function test_envia_los_campos_configurados_con_la_api_key(): void
    {
        $this->configurar([
            ['sitio' => 'nombre', 'crm' => 'name'],
            ['sitio' => 'celular', 'crm' => 'contact.phone'],
            ['sitio' => 'comuna', 'crm' => 'zone'],
            ['sitio' => 'intereses', 'crm' => 'volunteer_roles'],
        ]);
        Http::fake(['crm.campana.test/*' => Http::response(['data' => ['id' => 987]], 201)]);

        $token = $this->registrar()->json('token');
        $comuna = TerritorioComuna::query()->where('division', '2024')->where('codigo', 'C05')->value('id');
        $paso2 = $this->patchJson('/api/v1/registro/'.$token, ['comuna_id' => $comuna])->json('token');
        $this->patchJson('/api/v1/registro/'.$paso2, ['intereses' => ['testigo']])->assertOk();

        $this->sincronizar();

        Http::assertSent(fn (Request $r) => $r->url() === 'https://crm.campana.test/api/leads'
            && $r->hasHeader('Authorization', 'Bearer llave-secreta-123')
            && $r->hasHeader('X-Idempotencia'));
        Http::assertSent(fn (Request $r) => $r['name'] === 'Diana' && $r['contact']['phone'] === '+573001234567'
            && $r['zone'] === 'Comuna 5' && $r['volunteer_roles'] === 'Ser testigo electoral' && ! isset($r['uuid']));

        $ciudadano = Ciudadano::query()->sole();
        $this->assertSame('sincronizado', $ciudadano->crm_sync_estado);
        $this->assertSame('987', $ciudadano->crm_id);
        // Las interacciones no tienen URL configurada: esperan sin generar errores.
        $this->assertSame(0, CrmOutbox::query()->where('estado', 'error')->count());
        $this->assertTrue(CrmOutbox::query()->where('entidad', 'interaccion')->where('estado', 'pendiente')->exists());
    }

    public function test_sin_campos_envia_el_registro_completo(): void
    {
        $this->configurar();
        Http::fake(['crm.campana.test/*' => Http::response(['id' => 'a1'], 200)]);
        $this->registrar()->assertCreated();

        $this->sincronizar();

        Http::assertSent(fn (Request $r) => $r['nombre'] === 'Diana' && isset($r['uuid'], $r['consentimientos']));
    }

    public function test_si_el_crm_falla_queda_el_error_en_el_lead_y_se_reintenta(): void
    {
        $this->configurar();
        Http::fake(['crm.campana.test/*' => Http::response('No autorizado', 401)]);
        $this->registrar()->assertCreated();

        $this->sincronizar();

        $this->assertSame('error', Ciudadano::query()->sole()->crm_sync_estado);
        $fila = CrmOutbox::query()->where('entidad', 'ciudadano')->sole();
        $this->assertStringContainsString('HTTP 401', $fila->ultimo_error);
        $this->assertNotNull($fila->proximo_intento);
    }

    public function test_apagada_no_envia_nada_y_guarda_en_espera(): void
    {
        $this->configurar();
        $config = app(CrmConexion::class)->config();
        app(Ajustes::class)->set(CrmConexion::CLAVE, ['activo' => false] + $config, 'crm');
        Http::fake();
        $this->registrar()->assertCreated();

        $this->sincronizar();

        Http::assertNothingSent();
        $this->assertSame('pendiente', CrmOutbox::query()->where('entidad', 'ciudadano')->sole()->estado);
    }

    public function test_la_api_key_se_guarda_cifrada_y_no_queda_en_la_bitacora(): void
    {
        $this->configurar();

        $this->assertSame('llave-secreta-123', app(CrmConexion::class)->apiKey());
        $this->assertDatabaseMissing('ajustes', ['clave' => CrmConexion::CLAVE_API_KEY, 'valor' => json_encode('llave-secreta-123')]);
        $this->assertFalse(Activity::all()->contains(fn ($a) => str_contains(json_encode($a->properties), 'llave-secreta-123')));
    }

    public function test_solo_el_administrador_ve_la_pagina(): void
    {
        $admin = User::query()->create(['name' => 'Admin', 'email' => 'admin@prueba.co', 'password' => 'ClaveSegura2026', 'activo' => true]);
        $admin->assignRole('administrador');
        $editor = User::query()->create(['name' => 'Editor', 'email' => 'editor@prueba.co', 'password' => 'ClaveSegura2026', 'activo' => true]);
        $editor->assignRole('editor');

        $this->actingAs($editor)->get('/admin/conexion-crm')->assertForbidden();

        // Otra sesión: AuthenticateSession cierra la anterior al cambiar de usuario.
        $this->flushSession();
        $this->app['auth']->forgetGuards();
        $this->actingAs($admin)->get('/admin/conexion-crm')->assertOk()->assertSee('Conexión con el CRM')->assertSee('URL para registros');
    }
}
