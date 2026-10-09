<?php

namespace Tests\Feature;

use App\Models\Eje;
use App\Models\Pagina;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Cada bloque que el panel ofrece para las páginas nuevas se pinta en la página pública. */
class BloquesPaginaTest extends TestCase
{
    use RefreshDatabase;

    public function test_todos_los_bloques_generales_se_pintan(): void
    {
        Storage::fake('public');
        $foto = UploadedFile::fake()->image('foto.jpg', 400, 300)->store('paginas', 'public');
        $foto2 = UploadedFile::fake()->image('foto2.jpg', 400, 300)->store('paginas', 'public');

        Pagina::query()->create([
            'slug' => 'todos-los-bloques', 'titulo' => 'Todos los bloques', 'estado' => 'publicada',
            'bloques' => [
                ['type' => 'texto', 'data' => ['activo' => true, 'titulo' => 'Bloque de texto', 'contenido' => '<p>Contenido <strong>enriquecido</strong>.</p>']],
                ['type' => 'imagen', 'data' => ['activo' => true, 'imagen' => $foto, 'alt' => 'Imagen sola', 'pie' => 'Pie de la imagen']],
                ['type' => 'imagen_texto', 'data' => ['activo' => true, 'imagen' => $foto, 'alt' => 'Imagen con texto', 'etiqueta' => 'Rótulo IT', 'titulo' => 'Título imagen texto', 'texto' => '<p>Texto al lado.</p>', 'invertir' => true]],
                ['type' => 'cita', 'data' => ['activo' => true, 'texto' => 'Una cita de prueba', 'autor' => 'Autora de la cita']],
                ['type' => 'linea_tiempo', 'data' => ['activo' => true, 'etiqueta' => 'Raíces', 'titulo' => 'Trayectoria de prueba', 'items' => [['periodo' => '[POR CONFIRMAR]', 'titulo' => 'Hito de prueba', 'texto' => 'Detalle del hito']]]],
                ['type' => 'video', 'data' => ['activo' => true, 'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'titulo' => 'Video de prueba']],
                ['type' => 'galeria', 'data' => ['activo' => true, 'titulo' => 'Galería de prueba', 'imagenes' => [$foto, $foto2], 'alt' => 'Foto de la galería']],
                ['type' => 'cifras', 'data' => ['activo' => true, 'items' => [['valor' => '[POR CONFIRMAR]', 'texto' => 'Cifra de prueba']]]],
                ['type' => 'llamado', 'data' => ['activo' => true, 'etiqueta' => 'Súmate', 'titulo' => 'Llamado de prueba', 'texto' => 'Texto del llamado', 'boton_texto' => 'Botón del llamado', 'boton_url' => '/sumate']],
                ['type' => 'formulario', 'data' => ['activo' => true, 'tipo' => 'registro', 'titulo' => 'Formulario de registro']],
                ['type' => 'formulario', 'data' => ['activo' => true, 'tipo' => 'buzon', 'titulo' => 'Formulario buzón']],
                ['type' => 'ejes', 'data' => ['activo' => true, 'etiqueta' => 'Propuestas', 'titulo' => 'Ejes de prueba', 'texto' => 'Texto de ejes']],
                ['type' => 'comunas', 'data' => ['activo' => true, 'etiqueta' => 'Tu comuna', 'titulo' => 'Comunas de prueba', 'texto' => 'Texto de comunas']],
                ['type' => 'texto', 'data' => ['activo' => false, 'contenido' => '<p>Bloque oculto</p>']],
            ],
        ]);

        $respuesta = $this->get('/todos-los-bloques')->assertOk();

        $respuesta->assertSee('Bloque de texto')->assertSee('<strong>enriquecido</strong>', false)
            ->assertSee('/storage/'.$foto, false)->assertSee('alt="Imagen sola"', false)->assertSee('Pie de la imagen')
            ->assertSee('Título imagen texto')->assertSee('Texto al lado.')->assertSee('alt="Imagen con texto"', false)
            ->assertSee('Una cita de prueba')->assertSee('Autora de la cita')
            ->assertSee('Trayectoria de prueba')->assertSee('Hito de prueba')
            ->assertSee('Video de prueba')->assertSee('Reproducir el video')
            ->assertSee('Galería de prueba')->assertSee('/storage/'.$foto2, false)
            ->assertSee('Cifra de prueba')
            ->assertSee('Llamado de prueba')->assertSee('Botón del llamado')
            ->assertSee('Formulario de registro')->assertSee('Tu propuesta')
            ->assertSee('Comunas de prueba')->assertSee('Texto de comunas')
            ->assertDontSee('Bloque oculto');

        if (Eje::query()->publicados()->exists()) {
            $respuesta->assertSee('Ejes de <span class="ra-acento">prueba</span>', false);
        }

        // Las imágenes van con ruta relativa: se ven aunque el sitio se abra con otro dominio (www, 127.0.0.1).
        $respuesta->assertDontSee('http://localhost/storage/', false);
    }
}
