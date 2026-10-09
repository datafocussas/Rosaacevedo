<?php

namespace Tests\Feature;

use App\Models\PropuestaCiudadana;
use App\Models\Tema;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FotoPropuestaTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(string $rol): User
    {
        $usuario = User::query()->create(['name' => ucfirst($rol), 'email' => $rol.'@prueba.co', 'password' => 'ClaveSegura2026', 'activo' => true]);
        $usuario->assignRole($rol);

        return $usuario;
    }

    private function propuestaConFoto(): PropuestaCiudadana
    {
        Storage::fake('local');

        $this->post('/buzon', [
            'tema_id' => Tema::query()->value('id'),
            'texto' => 'El parque del barrio necesita luz en la noche.',
            'nombre' => 'Diana',
            'celular' => '3001234567',
            'consent' => ['general' => '1'],
            'foto' => UploadedFile::fake()->image('parque.jpg', 800, 600),
        ])->assertRedirect();

        return PropuestaCiudadana::query()->sole();
    }

    public function test_la_foto_se_guarda_en_el_disco_privado_y_se_ve_en_el_panel(): void
    {
        $propuesta = $this->propuestaConFoto();
        $foto = $propuesta->getFirstMedia('foto');

        $this->assertNotNull($foto);
        $this->assertSame('local', $foto->disk);

        $moderador = $this->usuario('moderador');
        $this->actingAs($moderador)->get(route('propuesta.foto', $propuesta))->assertOk()
            ->assertHeader('Content-Type', 'image/jpeg');
        $this->actingAs($moderador)->get('/admin/propuesta-ciudadanas/'.$propuesta->id.'/edit')->assertOk()
            ->assertSee(route('propuesta.foto', $propuesta), false);
    }

    public function test_la_foto_no_es_publica(): void
    {
        $propuesta = $this->propuestaConFoto();

        $this->get(route('propuesta.foto', $propuesta))->assertRedirect();
    }
}
