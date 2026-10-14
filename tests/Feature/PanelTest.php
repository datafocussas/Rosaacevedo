<?php

namespace Tests\Feature;

use App\Models\Ciudadano;
use App\Models\User;
use App\Services\ExportarRegistros;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class PanelTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(string $rol): User
    {
        $usuario = User::query()->create(['name' => ucfirst($rol), 'email' => $rol.'@prueba.co', 'password' => 'ClaveSegura2026', 'activo' => true]);
        $usuario->assignRole($rol);

        return $usuario;
    }

    /** Criterio 10: un Editor no puede ver datos personales. */
    public function test_el_editor_no_ve_registros(): void
    {
        $this->registrar()->assertCreated();
        $ciudadano = Ciudadano::query()->sole();

        $this->actingAs($this->usuario('editor'))
            ->get('/admin/ciudadanos')->assertForbidden();
        $this->get('/admin/ciudadanos/'.$ciudadano->id)->assertForbidden();
        $this->get('/admin/banners')->assertOk();
    }

    public function test_el_analista_ve_la_ficha_y_queda_en_la_bitacora(): void
    {
        $this->registrar()->assertCreated();
        $ciudadano = Ciudadano::query()->sole();
        $analista = $this->usuario('analista');

        $this->actingAs($analista)->get('/admin/ciudadanos/'.$ciudadano->id)->assertOk()->assertSee('300 123 4567');

        $this->assertTrue(Activity::query()->where('log_name', 'registros')->where('causer_id', $analista->id)->where('subject_id', $ciudadano->id)->exists());
    }

    public function test_el_moderador_ve_solo_nombre_y_comuna(): void
    {
        $this->registrar()->assertCreated();
        $ciudadano = Ciudadano::query()->sole();

        $this->actingAs($this->usuario('moderador'))->get('/admin/ciudadanos/'.$ciudadano->id)
            ->assertOk()->assertSee('Diana')->assertDontSee('300 123 4567');
    }

    /** Criterio 10: el Analista solo exporta con motivo y queda en la bitácora. */
    public function test_la_exportacion_exige_permiso_y_queda_en_la_bitacora(): void
    {
        $this->registrar()->assertCreated();
        $analista = $this->usuario('analista');
        $this->actingAs($analista);

        $respuesta = app(ExportarRegistros::class)->descargar($analista, ['motivo' => 'Convocatoria encuentro Comuna 4']);
        ob_start();
        $respuesta->sendContent();
        $csv = ob_get_clean();

        $this->assertStringContainsString('CONFIDENCIAL', $csv);
        $this->assertStringContainsString('Convocatoria encuentro Comuna 4', $csv);
        $this->assertStringContainsString('+573001234567', $csv);
        $registro = Activity::query()->where('log_name', 'exportaciones')->sole();
        $this->assertSame('Convocatoria encuentro Comuna 4', $registro->properties['motivo']);
        $this->assertSame(1, $registro->properties['registros']);

        $this->expectException(HttpException::class);
        app(ExportarRegistros::class)->descargar($this->usuario('editor'), ['motivo' => 'Prueba sin permiso']);
    }

    public function test_el_panel_exige_doble_factor(): void
    {
        config(['rosa.dos_factores' => true]);

        $this->actingAs($this->usuario('administrador'))->get('/admin')->assertRedirect(route('dos-factores.mostrar'));
        $this->get(route('dos-factores.mostrar'))->assertOk()->assertSee('Activa el doble factor');
    }
}
