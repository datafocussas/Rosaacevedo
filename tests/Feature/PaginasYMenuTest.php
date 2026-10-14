<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\Pagina;
use App\Services\MenuPaginas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaginasYMenuTest extends TestCase
{
    use RefreshDatabase;

    private function pagina(string $slug, string $estado = 'publicada'): Pagina
    {
        return Pagina::query()->create([
            'slug' => $slug,
            'titulo' => 'Nuestro equipo',
            'estado' => $estado,
            'bloques' => [['type' => 'texto', 'data' => ['contenido' => '<p>Quiénes trabajan con Rosa.</p>']]],
        ]);
    }

    public function test_una_pagina_publicada_se_abre_en_su_direccion(): void
    {
        $this->pagina('nuestro-equipo');

        $this->get('/nuestro-equipo')->assertOk()->assertSee('Nuestro equipo')->assertSee('Quiénes trabajan con Rosa.');
    }

    public function test_un_borrador_no_se_publica(): void
    {
        $this->pagina('nuestro-equipo', 'borrador');

        $this->get('/nuestro-equipo')->assertNotFound();
    }

    public function test_la_ruta_generica_no_tapa_las_secciones_del_sitio(): void
    {
        $this->get('/sumate')->assertOk()->assertSee('Súmate a la siembra');
        $this->get('/admin')->assertRedirect();
        $this->get('/up')->assertOk();
        $this->get('/inicio')->assertNotFound();
    }

    public function test_la_pagina_aparece_en_el_menu_elegido_y_sigue_su_direccion(): void
    {
        $pagina = $this->pagina('nuestro-equipo');
        $menu = app(MenuPaginas::class);

        $menu->sincronizar($pagina, ['pie_sitio'], 'Equipo');
        $this->assertDatabaseHas('menu_items', ['ubicacion' => 'pie_sitio', 'url' => '/nuestro-equipo', 'texto' => 'Equipo']);
        $this->get('/')->assertSee('href="/nuestro-equipo"', false);

        // Cambia la dirección: el enlace del menú la sigue.
        $pagina->update(['slug' => 'el-equipo']);
        $menu->sincronizar($pagina->fresh(), ['pie_sitio'], 'Equipo', '/nuestro-equipo');
        $this->assertDatabaseHas('menu_items', ['url' => '/el-equipo']);
        $this->assertDatabaseMissing('menu_items', ['url' => '/nuestro-equipo']);

        // Se quita del menú.
        $menu->sincronizar($pagina->fresh(), [], null);
        $this->assertSame(0, MenuItem::query()->where('url', '/el-equipo')->count());
    }

    public function test_el_menu_principal_admite_maximo_seis_items(): void
    {
        // Las semillas ya llenan el menú principal con 6 ítems.
        $this->assertFalse(app(MenuPaginas::class)->cabeEnPrincipal('/nuestro-equipo'));
        $this->assertTrue(app(MenuPaginas::class)->cabeEnPrincipal('/propuestas'));
    }

    public function test_al_borrar_la_pagina_sale_del_menu(): void
    {
        $pagina = $this->pagina('nuestro-equipo');
        app(MenuPaginas::class)->sincronizar($pagina, ['pie_sitio'], null);

        $pagina->delete();

        $this->assertSame(0, MenuItem::query()->where('url', '/nuestro-equipo')->count());
    }

    public function test_la_pagina_entra_al_mapa_del_sitio(): void
    {
        $this->pagina('nuestro-equipo');

        $this->get('/sitemap.xml')->assertSee(url('/nuestro-equipo'));
    }
}
