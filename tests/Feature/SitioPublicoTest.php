<?php

namespace Tests\Feature;

use App\Models\Ajuste;
use App\Models\Redireccion;
use App\Services\Ajustes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitioPublicoTest extends TestCase
{
    use RefreshDatabase;

    public function test_las_paginas_publicas_responden(): void
    {
        foreach (['/', '/sumate', '/buzon', '/propuestas', '/propuestas/salud', '/conoce-a-rosa', '/manifiesto', '/uso-de-ia',
            '/politica-de-datos', '/mis-datos', '/noticias', '/agenda', '/comunas', '/comunas/el-manzanillo', '/enlaces', '/sitemap.xml', '/robots.txt'] as $ruta) {
            $this->get($ruta)->assertOk();
        }
    }

    public function test_las_siete_comunas_y_el_corregimiento_aparecen_en_inicio(): void
    {
        $this->get('/')->assertSeeInOrder(['Comuna 1', 'Comuna 7', 'El Manzanillo'])->assertSee('Corregimiento');
    }

    public function test_en_precampana_no_se_pide_el_voto(): void
    {
        foreach (['/', '/sumate', '/propuestas'] as $ruta) {
            $this->get($ruta)->assertDontSee('vota por', false)->assertDontSee('Vota por', false);
        }
        $this->get('/transparencia')->assertNotFound();
    }

    public function test_los_datos_pendientes_se_marcan_visibles(): void
    {
        $this->get('/conoce-a-rosa')->assertSee('[POR CONFIRMAR]');
    }

    public function test_redireccion_301_administrable(): void
    {
        Redireccion::query()->create(['desde' => '/quienes-somos', 'hacia' => '/conoce-a-rosa', 'codigo' => 301]);

        $this->get('/quienes-somos/')->assertStatus(301)->assertRedirect('/conoce-a-rosa');
        $this->assertSame(1, Redireccion::query()->value('visitas'));
    }

    public function test_el_boton_de_whatsapp_lleva_el_codigo_de_atribucion(): void
    {
        app(Ajustes::class)->set('whatsapp_numero', '573001112233');

        $enlace = app(Ajustes::class)->enlaceWhatsapp('R-ABC123');

        $this->assertStringStartsWith('https://wa.me/573001112233?text=', $enlace);
        $this->assertStringContainsString(rawurlencode('Código: R-ABC123'), $enlace);
    }

    public function test_los_ajustes_quedan_en_la_bitacora(): void
    {
        app(Ajustes::class)->set('hashtag', '#OtroHashtag');

        $this->assertDatabaseHas('activity_log', ['log_name' => 'configuracion', 'description' => 'Cambio de configuración: hashtag']);
        $this->assertSame('#OtroHashtag', Ajuste::query()->find('hashtag')->valor);
    }
}
