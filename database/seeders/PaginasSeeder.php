<?php

namespace Database\Seeders;

use App\Models\Pagina;
use Illuminate\Database\Seeder;

/**
 * Páginas base construidas por bloques. Todo dato biográfico o cifra no confirmada va con el
 * marcador visible [POR CONFIRMAR] (regla 1); Comunicaciones lo reemplaza desde el panel.
 */
class PaginasSeeder extends Seeder
{
    public function run(): void
    {
        $this->crear('inicio', 'Inicio', [
            ['type' => 'registro', 'data' => ['activo' => true, 'etiqueta_formulario' => 'Súmate a la siembra', 'titulo_formulario' => 'Recibe las propuestas para tu barrio']],
            ['type' => 'ejes', 'data' => ['activo' => true, 'etiqueta' => 'Propuestas', 'titulo' => 'Aquí me planto por…', 'texto' => 'No prometemos veinte obras. Nos comprometemos con un propósito: que ningún joven tenga que irse de Itagüí para cumplir sus sueños.', 'boton_texto' => 'Conoce las propuestas']],
            ['type' => 'raices', 'data' => ['activo' => true, 'etiqueta' => 'Raíces', 'titulo' => 'Rosa no se trasplanta', 'texto' => '[POR CONFIRMAR] Años de servicio y cargos de Rosa según su hoja de vida oficial.', 'enlace_texto' => 'Lee el manifiesto', 'enlace_url' => '/manifiesto']],
            ['type' => 'cifras', 'data' => ['activo' => false, 'items' => [
                ['valor' => '[POR CONFIRMAR]', 'texto' => 'años de servicio público'],
                ['valor' => '84', 'texto' => 'barrios y 8 veredas que recorremos'],
            ]]],
            ['type' => 'comunas', 'data' => ['activo' => true, 'etiqueta' => 'Tu comuna', 'titulo' => 'Aquí me planto en cada barrio']],
            ['type' => 'buzon', 'data' => ['activo' => true, 'etiqueta' => 'Buzón ciudadano', 'titulo' => '¿Qué necesita tu barrio?', 'texto' => 'Tu propuesta entra al programa de gobierno. Te contamos qué pasó con ella.', 'boton_texto' => 'Deja tu propuesta']],
            ['type' => 'noticias', 'data' => ['activo' => true, 'titulo' => 'Noticias']],
            ['type' => 'agenda', 'data' => ['activo' => true, 'etiqueta' => 'Agenda', 'titulo' => 'Nos vemos en el barrio']],
            ['type' => 'redes', 'data' => ['activo' => true]],
        ], 'Rosa Acevedo · Por el futuro de Itagüí', 'Sitio oficial de Rosa María Acevedo Jaramillo. Aquí me planto por el futuro de Itagüí.');

        $this->crear('conoce-a-rosa', 'Conoce a Rosa', [
            ['type' => 'imagen_texto', 'data' => ['etiqueta' => 'Rosa María Acevedo Jaramillo', 'titulo' => 'Sus raíces están aquí', 'texto' => '<p>[POR CONFIRMAR] Biografía corta de Rosa según su hoja de vida oficial: origen, familia y relación con Itagüí.</p>']],
            ['type' => 'linea_tiempo', 'data' => ['etiqueta' => 'Raíces', 'titulo' => 'Trayectoria', 'items' => [
                ['periodo' => '[POR CONFIRMAR]', 'titulo' => '[POR CONFIRMAR] Cargo', 'texto' => 'Cada cargo y periodo se publica solo con la hoja de vida oficial.'],
            ]]],
            ['type' => 'texto', 'data' => ['titulo' => 'Formación', 'contenido' => '<p>[POR CONFIRMAR] Formación académica.</p>']],
            ['type' => 'cita', 'data' => ['texto' => 'Mis raíces están aquí. Y las raíces no se trasplantan.', 'autor' => 'Rosa Acevedo']],
        ], 'Conoce a Rosa', 'Quién es Rosa María Acevedo Jaramillo y por qué se planta por Itagüí.');

        $this->crear('manifiesto', 'Rosa no se trasplanta', [
            ['type' => 'texto', 'data' => ['contenido' => '<p><strong>Aquí me planto.</strong> Por el futuro de Itagüí.</p><p>Raíces que permanecen. Compromisos que cumplimos.</p><p>Mis raíces están aquí. Y las raíces no se trasplantan.</p><p>[POR CONFIRMAR] Texto completo del manifiesto, versión final de Comunicaciones.</p><p>#AquíMePlantoPorItagüí · #RosaAlcaldesa</p>']],
        ], 'Manifiesto · Rosa no se trasplanta', 'Aquí me planto. Por el futuro de Itagüí. El manifiesto de Rosa Acevedo.');

        $this->crear('uso-de-ia', 'Uso de inteligencia artificial', [
            ['type' => 'texto', 'data' => ['contenido' => '<p>Te contamos qué hacemos con herramientas de inteligencia artificial en este sitio y quién revisa su trabajo (Circular Externa SIC 002 de 2026).</p>'
                .'<h2>Qué se automatiza</h2><ul><li>Sugerir el tema, la comuna, el tono y un resumen de cada propuesta que llega al buzón ciudadano.</li></ul>'
                .'<h2>Qué datos recibe la herramienta</h2><p>Solo el texto de la propuesta. Nunca tu nombre, tu celular ni tu correo.</p>'
                .'<h2>Quién revisa</h2><p>Una persona del equipo de Escucha confirma o corrige cada sugerencia antes de usarla. La herramienta no toma decisiones sobre ti ni te responde de forma automática.</p>'
                .'<h2>Qué no hacemos</h2><ul><li>No usamos inteligencia artificial para perfilarte por afinidad política.</li><li>No enviamos mensajes automáticos desde este sitio.</li></ul>'
                .'<p>Si tienes preguntas, escríbenos desde la página Mis datos.</p>']],
        ], 'Uso de inteligencia artificial', 'Qué automatizamos con inteligencia artificial y quién lo revisa.');
    }

    private function crear(string $slug, string $titulo, array $bloques, string $seoTitulo, string $seoDescripcion): void
    {
        Pagina::query()->firstOrCreate(['slug' => $slug], [
            'titulo' => $titulo,
            'bloques' => $bloques,
            'seo_titulo' => mb_substr($seoTitulo, 0, 70),
            'seo_descripcion' => mb_substr($seoDescripcion, 0, 160),
            'estado' => 'publicada',
        ]);
    }
}
