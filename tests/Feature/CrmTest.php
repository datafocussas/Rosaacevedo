<?php

namespace Tests\Feature;

use App\Contracts\CrmCliente;
use App\Jobs\SincronizarCrm;
use App\Models\Ciudadano;
use App\Models\CrmOutbox;
use App\Models\User;
use App\Notifications\FalloSincronizacionCrm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CrmTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['rosa.crm.driver' => 'http', 'rosa.crm.url' => 'https://crm.prueba/api', 'rosa.crm.secreto' => 'secreto']);
    }

    /** Criterio 4: con el CRM apagado los registros se guardan y se sincronizan solos cuando vuelve. */
    public function test_con_el_crm_caido_se_guarda_y_luego_se_sincroniza(): void
    {
        $crmArriba = false;
        Http::fake(['crm.prueba/*' => function () use (&$crmArriba) {
            return $crmArriba ? Http::response(['id' => 'crm-123'], 201) : Http::response('caído', 503);
        }]);
        $this->registrar()->assertCreated();

        app(SincronizarCrm::class)->handle(app(CrmCliente::class));

        $this->assertSame(1, Ciudadano::query()->count());
        $this->assertSame(0, CrmOutbox::query()->where('estado', 'enviado')->count());
        $fila = CrmOutbox::query()->where('entidad', 'ciudadano')->sole();
        $this->assertSame('error', $fila->estado);
        $this->assertSame(1, $fila->intentos);
        $this->assertTrue($fila->proximo_intento->between(now()->addSeconds(50), now()->addMinutes(2)));

        // Vuelve el CRM y pasa el tiempo de espera.
        $crmArriba = true;
        $this->travel(2)->minutes();
        app(SincronizarCrm::class)->handle(app(CrmCliente::class));

        $this->assertSame(0, CrmOutbox::query()->where('estado', '!=', 'enviado')->count());
        $ciudadano = Ciudadano::query()->sole();
        $this->assertSame('sincronizado', $ciudadano->crm_sync_estado);
        $this->assertSame('crm-123', $ciudadano->crm_id);
    }

    public function test_firma_el_cuerpo_y_envia_la_llave_de_idempotencia(): void
    {
        Http::fake(['crm.prueba/*' => Http::response(['id' => 'x'], 200)]);
        $this->registrar()->assertCreated();

        app(SincronizarCrm::class)->handle(app(CrmCliente::class));

        Http::assertSent(function ($request) {
            return $request->url() === 'https://crm.prueba/api/ingesta/ciudadano'
                && $request->header('X-Firma')[0] === 'sha256='.hash_hmac('sha256', $request->body(), 'secreto')
                && filled($request->header('X-Idempotencia')[0] ?? null);
        });
    }

    public function test_alerta_al_administrador_en_el_quinto_fallo(): void
    {
        Notification::fake();
        Http::fake(['crm.prueba/*' => Http::response('error', 500)]);
        $admin = User::query()->create(['name' => 'Admin', 'email' => 'a@a.co', 'password' => 'x', 'activo' => true]);
        $admin->assignRole('administrador');
        $this->registrar()->assertCreated();

        foreach (range(1, 5) as $intento) {
            CrmOutbox::query()->update(['proximo_intento' => now()->subSecond()]);
            app(SincronizarCrm::class)->handle(app(CrmCliente::class));
        }

        Notification::assertSentTo($admin, FalloSincronizacionCrm::class);
        $this->assertSame(5, CrmOutbox::query()->where('entidad', 'ciudadano')->value('intentos'));
        $this->assertTrue(CrmOutbox::query()->where('entidad', 'ciudadano')->value('proximo_intento') > now()->addHours(5));
    }

    public function test_sin_crm_configurado_las_filas_esperan_pendientes(): void
    {
        config(['rosa.crm.driver' => 'nulo']);
        Http::fake();
        $this->registrar()->assertCreated();

        app(SincronizarCrm::class)->handle(app(CrmCliente::class));

        Http::assertNothingSent();
        $this->assertSame(0, CrmOutbox::query()->where('estado', '!=', 'pendiente')->count());
    }
}
