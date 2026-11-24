<?php

namespace Tests\Feature;

use App\Models\Noticia;
use App\Models\Pagina;
use App\Models\RedSocial;
use App\Support\Incrustacion;
use App\Support\Texto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncrustacionTest extends TestCase
{
    use RefreshDatabase;

    public function test_reconoce_videos_y_publicaciones_de_cada_red(): void
    {
        $casos = [
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ' => ['video', 'youtube', 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?rel=0'],
            'https://youtu.be/dQw4w9WgXcQ?si=abc' => ['video', 'youtube', 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?rel=0'],
            'https://www.youtube.com/shorts/dQw4w9WgXcQ' => ['video', 'youtube', 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?rel=0'],
            'https://vimeo.com/76979871' => ['video', 'vimeo', 'https://player.vimeo.com/video/76979871?dnt=1'],
            'https://www.instagram.com/p/C1a2B3c4D5e/' => ['red', 'instagram', 'https://www.instagram.com/p/C1a2B3c4D5e/embed/captioned/'],
            'https://www.instagram.com/reel/C1a2B3c4D5e/?igsh=xyz' => ['red', 'instagram', 'https://www.instagram.com/reel/C1a2B3c4D5e/embed/captioned/'],
            'https://www.tiktok.com/@rosaacevedoj/video/7312345678901234567' => ['red', 'tiktok', 'https://www.tiktok.com/embed/v2/7312345678901234567?lang=es'],
            'https://x.com/rosaacevedoj/status/1850000000000000000' => ['red', 'x', 'https://platform.twitter.com/embed/Tweet.html?id=1850000000000000000&dnt=true&lang=es'],
            'https://twitter.com/rosaacevedoj/status/1850000000000000000' => ['red', 'x', 'https://platform.twitter.com/embed/Tweet.html?id=1850000000000000000&dnt=true&lang=es'],
        ];

        foreach ($casos as $url => [$tipo, $red, $src]) {
            $datos = Incrustacion::de($url);
            $this->assertNotNull($datos, $url);
            $this->assertSame([$tipo, $red, $src], [$datos['tipo'], $datos['red'], $datos['src']], $url);
        }

        $this->assertSame('https://i.ytimg.com/vi/dQw4w9WgXcQ/hqdefault.jpg', Incrustacion::de('https://youtu.be/dQw4w9WgXcQ')['miniatura']);
        $this->assertTrue(Incrustacion::de('https://www.youtube.com/shorts/dQw4w9WgXcQ')['vertical']);

        $facebook = Incrustacion::de('https://www.facebook.com/rosaacevedoj/posts/pfbid0abc');
        $this->assertStringStartsWith('https://www.facebook.com/plugins/post.php?href=https%3A%2F%2Fwww.facebook.com%2Frosaacevedoj%2Fposts%2Fpfbid0abc', $facebook['src']);
        $this->assertStringStartsWith('https://www.facebook.com/plugins/video.php?', Incrustacion::de('https://www.facebook.com/reel/123456789')['src']);

        foreach (['https://www.facebook.com/rosaacevedoj', 'https://rosaacevedo.com/noticias', 'https://www.instagram.com/rosaacevedoj/', 'javascript:alert(1)', ''] as $url) {
            $this->assertNull(Incrustacion::de($url), $url);
        }
    }

    public function test_widgets_de_perfil(): void
    {
        $this->assertSame('https://www.instagram.com/rosaacevedoj/embed/', Incrustacion::perfil('https://www.instagram.com/rosaacevedoj/')['src']);
        $this->assertSame('https://www.tiktok.com/embed/@rosaacevedoj?lang=es', Incrustacion::perfil('https://www.tiktok.com/@rosaacevedoj')['src']);
        $this->assertStringStartsWith('https://www.facebook.com/plugins/page.php?href=https%3A%2F%2Fwww.facebook.com%2Frosaacevedoj', Incrustacion::perfil('https://www.facebook.com/rosaacevedoj')['src']);
        $this->assertNull(Incrustacion::perfil('https://x.com/rosaacevedoj'));
    }

    public function test_un_enlace_pegado_solo_se_ve_incrustado_y_un_enlace_con_palabras_no(): void
    {
        $html = (string) Texto::enriquecido(
            '<p>Mira el recorrido:</p><p><a href="https://youtu.be/dQw4w9WgXcQ">https://youtu.be/dQw4w9WgXcQ</a></p>'
            .'<div>Antes<br><a href="https://www.instagram.com/p/C1a2B3c4D5e/">https://www.instagram.com/p/C1a2B3c4D5e/</a><br>después</div>'
            .'<p>Lee <a href="https://www.youtube.com/watch?v=aaaaaaaaaaa">la entrevista</a> completa.</p>'
        );

        $this->assertStringContainsString('<p>Mira el recorrido:</p>', $html);
        $this->assertStringContainsString('Reproducir el video de YouTube', $html);
        $this->assertStringContainsString('https://i.ytimg.com/vi/dQw4w9WgXcQ/hqdefault.jpg', $html);
        $this->assertStringNotContainsString('<p><a href="https://youtu.be/dQw4w9WgXcQ"', $html);
        // En medio de un texto: el párrafo se conserva y la publicación va después.
        $this->assertStringContainsString('Antes', $html);
        $this->assertStringContainsString('Publicación de Instagram', $html);
        $this->assertStringContainsString('https://www.instagram.com/p/C1a2B3c4D5e/embed/captioned/', $html);
        $this->assertLessThan(strpos($html, 'Publicación de Instagram'), strpos($html, 'después'));
        // Palabras enlazadas: queda como enlace normal.
        $this->assertStringContainsString('la entrevista</a>', $html);
        $this->assertStringNotContainsString('aaaaaaaaaaa?rel=0', $html);
    }

    public function test_las_imagenes_del_editor_se_conservan_y_los_scripts_no(): void
    {
        $html = (string) Texto::enriquecido('<figure><img src="/storage/noticias/foto.jpg" alt="Recorrido"><figcaption>Santa María</figcaption></figure><script>alert(1)</script><p onclick="x()">Hola</p>');

        $this->assertStringContainsString('<img src="/storage/noticias/foto.jpg" alt="Recorrido"', $html);
        $this->assertStringContainsString('<figcaption>Santa María</figcaption>', $html);
        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('onclick', $html);
    }

    public function test_la_noticia_muestra_el_video_y_la_csp_permite_los_marcos(): void
    {
        config(['csp.enabled' => true]);
        $noticia = Noticia::query()->create([
            'titulo' => 'Recorrido por San Pío', 'slug' => 'recorrido', 'estado' => 'publicada', 'publicada_en' => now()->subHour(),
            'cuerpo' => '<p>Texto.</p><p><a href="https://www.tiktok.com/@rosaacevedoj/video/7312345678901234567">https://www.tiktok.com/@rosaacevedoj/video/7312345678901234567</a></p>',
        ]);

        $respuesta = $this->get('/noticias/'.$noticia->slug)->assertOk()
            ->assertSee('Publicación de TikTok')
            ->assertSee('Mostrar la publicación aquí')
            ->assertSee('https://www.tiktok.com/embed/v2/7312345678901234567?lang=es', false);

        $csp = $respuesta->headers->get('Content-Security-Policy');
        foreach (Incrustacion::MARCOS as $origen) {
            $this->assertStringContainsString($origen, $csp);
        }
        $this->assertStringContainsString('https://i.ytimg.com', $csp);
    }

    public function test_el_bloque_de_redes_muestra_perfiles_y_publicaciones(): void
    {
        RedSocial::query()->where('icono', 'instagram')->update(['url' => 'https://www.instagram.com/rosaacevedoj/', 'activa' => true]);

        $pagina = Pagina::query()->where('slug', 'en-redes')->sole();
        $this->assertSame('borrador', $pagina->estado);

        $bloques = $pagina->bloques;
        $bloques[0]['data']['publicaciones'] = ['https://x.com/rosaacevedoj/status/1850000000000000000', 'https://rosaacevedo.com/no-se-puede'];
        $pagina->update(['bloques' => $bloques, 'estado' => 'publicada']);

        $this->get('/en-redes')->assertOk()
            ->assertSee('Lo que pasa en nuestras redes')
            ->assertSee('https://www.instagram.com/rosaacevedoj/embed/', false)
            ->assertSee('Perfil de Instagram')
            ->assertSee('Mostrar el perfil aquí')
            ->assertSee('Publicación de X')
            ->assertDontSee('no-se-puede');
    }
}
