<?php

namespace Tests\Feature;

use App\Models\Columna;
use App\Models\Columnista;
use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class OpinionTest extends TestCase
{
    use RefreshDatabase;

    private function columna(array $datos = []): Columna
    {
        $columnista = Columnista::query()->create(['nombre' => 'Marta Restrepo', 'slug' => 'marta-restrepo', 'perfil' => 'Lideresa comunal de San Pío X']);

        return $columnista->columnas()->create($datos + [
            'titulo' => 'El parque que nos falta',
            'slug' => 'el-parque-que-nos-falta',
            'resumen' => 'Un barrio sin parque es un barrio sin encuentro.',
            'cuerpo' => '<p>Texto de la columna.</p>',
            'estado' => 'publicada',
            'publicada_en' => now()->subHour(),
        ]);
    }

    public function test_la_columna_publicada_se_ve_con_su_columnista(): void
    {
        $columna = $this->columna();

        $this->get('/opinion')->assertOk()->assertSee('El parque que nos falta')->assertSee('Marta Restrepo')->assertSee('MR');
        $this->get('/opinion/'.$columna->slug)->assertOk()
            ->assertSee('Texto de la columna.', false)
            ->assertSee('Lideresa comunal de San Pío X')
            ->assertSee('OpinionNewsArticle', false)
            ->assertSee('expresan el pensamiento de quienes las firman');
    }

    public function test_un_borrador_o_una_programada_futura_no_se_publican(): void
    {
        $borrador = $this->columna(['estado' => 'borrador']);
        $this->get('/opinion/'.$borrador->slug)->assertNotFound();
        $this->get('/opinion')->assertDontSee('El parque que nos falta');

        $borrador->update(['estado' => 'programada', 'publicada_en' => now()->addDay()]);
        $this->get('/opinion/'.$borrador->slug)->assertNotFound();

        $this->get(URL::temporarySignedRoute('vista-previa.columna', now()->addHour(), ['columna' => $borrador->id]))
            ->assertOk()->assertSee('Vista previa')->assertHeader('X-Robots-Tag', 'noindex');
    }

    public function test_opinion_aparece_en_el_pie_y_en_el_mapa_del_sitio(): void
    {
        $columna = $this->columna();

        $this->assertTrue(MenuItem::query()->where('url', '/opinion')->where('ubicacion', 'pie_sitio')->exists());
        $this->get('/sitemap.xml')->assertOk()->assertSee(route('opinion.show', $columna));
    }

    public function test_el_bloque_de_opinion_muestra_las_ultimas_columnas(): void
    {
        $this->columna();
        $html = view('partials.bloques.opinion', ['d' => ['titulo' => 'Opinión'], 'clasesFondo' => 'ra-fondo-blanco'])->render();

        $this->assertStringContainsString('El parque que nos falta', $html);
    }

    public function test_el_editor_gestiona_columnas_en_el_panel(): void
    {
        $this->columna();
        $editor = User::query()->create(['name' => 'Editor', 'email' => 'editor@prueba.co', 'password' => 'ClaveSegura2026', 'activo' => true]);
        $editor->assignRole('editor');

        $this->actingAs($editor);
        $this->get('/admin/columnas')->assertOk()->assertSee('El parque que nos falta');
        $this->get('/admin/columnas/create')->assertOk();
        $this->get('/admin/columnistas')->assertOk()->assertSee('Marta Restrepo');
    }
}
