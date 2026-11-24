<?php

namespace Tests\Feature;

use App\Contracts\ModeloAsistente;
use App\Models\Ajuste;
use App\Models\ConocimientoAsistente;
use App\Models\ConversacionAsistente;
use App\Models\Eje;
use App\Models\MensajeAsistente;
use App\Models\User;
use App\Services\Ajustes;
use App\Services\Asistente\AsistenteConfig;
use App\Services\Asistente\ClaudeModelo;
use App\Services\Asistente\ExtraerTexto;
use App\Services\Asistente\RespuestaModelo;
use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class AsistenteTest extends TestCase
{
    use RefreshDatabase;

    /** Llamadas que recibió el modelo simulado: [sistema, mensajes]. */
    private array $llamadas = [];

    private function modelo(RespuestaModelo|\Throwable|null $respuesta = null): void
    {
        $this->app->instance(ModeloAsistente::class, new class($this->llamadas, $respuesta) implements ModeloAsistente
        {
            public function __construct(private array &$llamadas, private RespuestaModelo|\Throwable|null $respuesta) {}

            public function responder(array $sistema, array $mensajes): RespuestaModelo
            {
                $this->llamadas[] = [$sistema, $mensajes];
                if ($this->respuesta instanceof \Throwable) {
                    throw $this->respuesta;
                }

                return $this->respuesta ?? new RespuestaModelo('Rosa es aspirante a la Alcaldía de Itagüí.', 1200, 40);
            }
        });
    }

    private function encender(array $config = []): void
    {
        app(AsistenteConfig::class)->guardarApiKey('sk-ant-prueba-123');
        app(Ajustes::class)->set(AsistenteConfig::CLAVE, $config + ['activo' => true], 'asistente');
    }

    private function preguntar(string $mensaje, ?string $conversacion = null)
    {
        return $this->postJson('/api/v1/asistente', ['mensaje' => $mensaje, 'conversacion' => $conversacion, 'pagina' => '/propuestas']);
    }

    public function test_apagado_o_sin_api_key_no_aparece_ni_responde(): void
    {
        $this->modelo();
        $this->get('/')->assertOk()->assertDontSee('Pregúntale al asistente');
        $this->preguntar('Hola')->assertNotFound();

        app(Ajustes::class)->set(AsistenteConfig::CLAVE, ['activo' => true], 'asistente');
        $this->get('/')->assertDontSee('Pregúntale al asistente');

        app(AsistenteConfig::class)->guardarApiKey('sk-ant-prueba-123');
        $this->get('/')->assertSee('Pregúntale al asistente')->assertSee('No compartas datos personales');
    }

    public function test_responde_con_la_base_de_conocimiento_y_guarda_la_conversacion(): void
    {
        $this->modelo();
        $this->encender();
        ConocimientoAsistente::query()->create(['titulo' => 'Trayectoria', 'categoria' => 'trayectoria', 'contenido' => 'Rosa nació en Itagüí.', 'fuente' => 'Hoja de vida oficial']);
        ConocimientoAsistente::query()->create(['titulo' => 'Borrador', 'contenido' => 'Dato sin revisar', 'activo' => false]);
        Eje::query()->first()->update(['publicado' => true, 'compromisos' => [['texto' => 'Más horas de atención en los puestos de salud.']]]);

        $respuesta = $this->preguntar('¿Quién es Rosa?')->assertOk()
            ->assertJsonPath('respuesta', 'Rosa es aspirante a la Alcaldía de Itagüí.')
            ->assertJsonPath('estado', 'ok');
        $uuid = $respuesta->json('conversacion');

        [$sistema, $mensajes] = $this->llamadas[0];
        $this->assertStringContainsString('Nunca inventes', $sistema[0]['text']);
        $this->assertStringContainsString('no pidas el voto', $sistema[0]['text']);
        $this->assertStringContainsString('Rosa nació en Itagüí.', $sistema[1]['text']);
        $this->assertStringContainsString('Fuente: Hoja de vida oficial', $sistema[1]['text']);
        $this->assertStringContainsString('Más horas de atención en los puestos de salud.', $sistema[1]['text']);
        $this->assertStringNotContainsString('Dato sin revisar', $sistema[1]['text']);
        $this->assertSame(['type' => 'ephemeral'], $sistema[1]['cacheControl']);
        $this->assertSame([['role' => 'user', 'content' => '¿Quién es Rosa?']], $mensajes);

        // La segunda pregunta viaja con la conversación anterior.
        $this->preguntar('¿Y qué propone?', $uuid)->assertOk()->assertJsonPath('conversacion', $uuid);
        $this->assertCount(3, $this->llamadas[1][1]);
        $this->assertSame('assistant', $this->llamadas[1][1][1]['role']);

        $conversacion = ConversacionAsistente::query()->sole();
        $this->assertSame(2, $conversacion->mensajes);
        $this->assertSame('/propuestas', $conversacion->pagina);
        $this->assertSame(2400, $conversacion->tokens_entrada);
        $this->assertSame(4, MensajeAsistente::query()->count());
        $this->assertNotSame(request()->ip(), $conversacion->ip_hash);
    }

    public function test_las_indicaciones_de_tono_se_suman_sin_reemplazar_las_reglas(): void
    {
        $this->modelo();
        $this->encender(['instrucciones' => 'Habla como alguien del barrio.']);
        $this->preguntar('Hola')->assertOk();

        $reglas = $this->llamadas[0][0][0]['text'];
        $this->assertStringContainsString('Habla como alguien del barrio.', $reglas);
        $this->assertStringContainsString('No das cifras de encuestas', $reglas);
    }

    public function test_en_campana_no_aplica_la_regla_de_precampana(): void
    {
        $this->modelo();
        $this->encender();
        app(Ajustes::class)->set('modo_sitio', 'campana');
        $this->preguntar('Hola')->assertOk();

        $this->assertStringContainsString('época de campaña', $this->llamadas[0][0][0]['text']);
    }

    public function test_los_limites_cortan_sin_llamar_al_modelo(): void
    {
        $this->modelo();
        $this->encender(['max_por_conversacion' => 5, 'max_diario' => 10]);

        $uuid = $this->preguntar('Uno')->json('conversacion');
        ConversacionAsistente::query()->where('uuid', $uuid)->update(['mensajes' => 5]);
        $this->preguntar('Otra más', $uuid)->assertOk()->assertJsonPath('estado', 'limite');
        $this->assertCount(1, $this->llamadas);

        MensajeAsistente::query()->insert(array_fill(0, 10, ['conversacion_id' => ConversacionAsistente::query()->value('id'), 'rol' => 'usuario', 'contenido' => 'x', 'estado' => 'ok', 'created_at' => now()]));
        $this->preguntar('Nueva conversación')->assertOk()->assertJsonPath('estado', 'limite');
        $this->assertCount(1, $this->llamadas);
    }

    public function test_si_el_modelo_falla_responde_con_una_salida_y_no_lo_repite_en_el_historial(): void
    {
        $this->modelo(new RuntimeException('Sin conexión'));
        $this->encender();

        $uuid = $this->preguntar('¿Quién es Rosa?')->assertOk()->assertJsonPath('estado', 'error')->json('conversacion');
        $this->assertStringContainsString('/buzon', MensajeAsistente::query()->where('rol', 'asistente')->value('contenido'));

        $this->modelo();
        $this->preguntar('Hola de nuevo', $uuid)->assertOk()->assertJsonPath('estado', 'ok');
        $this->assertSame([['role' => 'user', 'content' => 'Hola de nuevo']], $this->llamadas[1][1]);
    }

    public function test_una_pregunta_declinada_recibe_una_respuesta_amable(): void
    {
        $this->modelo(new RespuestaModelo('', 100, 0, true));
        $this->encender();

        $this->preguntar('Algo fuera de lugar')->assertOk()->assertJsonPath('estado', 'rechazo');
    }

    public function test_valida_la_pregunta_y_limita_la_frecuencia(): void
    {
        $this->modelo();
        $this->encender();

        $this->preguntar('')->assertUnprocessable()->assertJsonPath('errors.mensaje.0', 'Escribe tu pregunta.');
        $this->preguntar(str_repeat('a', 601))->assertUnprocessable();

        // Las dos preguntas inválidas también cuentan: 8 por minuto en total.
        for ($i = 0; $i < 6; $i++) {
            $this->preguntar('Pregunta '.$i)->assertOk();
        }
        $this->preguntar('Una más')->assertStatus(429);
    }

    public function test_la_api_key_se_guarda_cifrada_y_fuera_de_la_bitacora(): void
    {
        app(AsistenteConfig::class)->guardarApiKey('sk-ant-secreta-999');

        $guardada = Ajuste::query()->find(AsistenteConfig::CLAVE_API_KEY)->valor;
        $this->assertStringNotContainsString('sk-ant-secreta-999', (string) $guardada);
        $this->assertSame('sk-ant-secreta-999', app(AsistenteConfig::class)->apiKey());
        $this->assertStringNotContainsString('sk-ant-secreta-999', Activity::query()->get()->toJson());
    }

    public function test_claude_recibe_el_modelo_las_reglas_en_cache_y_el_respaldo(): void
    {
        $this->encender(['modelo' => 'claude-opus-5-5', 'esfuerzo' => 'low']);
        $historial = [];
        $pila = HandlerStack::create(new MockHandler([new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'id' => 'msg_1', 'type' => 'message', 'role' => 'assistant', 'model' => 'claude-opus-5-5',
            'content' => [['type' => 'text', 'text' => 'Hola, te cuento.']],
            'stop_reason' => 'end_turn', 'stop_sequence' => null, 'stop_details' => null, 'context_management' => null, 'container' => null,
            'usage' => ['input_tokens' => 50, 'output_tokens' => 12, 'cache_read_input_tokens' => 900, 'cache_creation_input_tokens' => 0],
        ]))]));
        $pila->push(Middleware::history($historial));

        $respuesta = (new ClaudeModelo(app(AsistenteConfig::class), new Guzzle(['handler' => $pila])))->responder(
            [['type' => 'text', 'text' => 'Reglas'], ['type' => 'text', 'text' => 'Base', 'cacheControl' => ['type' => 'ephemeral']]],
            [['role' => 'user', 'content' => 'Hola']],
        );

        $this->assertSame('Hola, te cuento.', $respuesta->texto);
        $this->assertSame(950, $respuesta->tokensEntrada);
        $this->assertSame(12, $respuesta->tokensSalida);
        $this->assertFalse($respuesta->rechazada);

        $peticion = $historial[0]['request'];
        $cuerpo = json_decode((string) $peticion->getBody(), true);
        $this->assertStringEndsWith('/v1/messages', $peticion->getUri()->getPath());
        $this->assertSame('Bearer sk-ant-prueba-123', $peticion->getHeaderLine('Authorization'));
        $this->assertStringContainsString('server-side-fallback-2026-07-01', $peticion->getHeaderLine('anthropic-beta'));
        $this->assertSame('claude-opus-5-5', $cuerpo['model']);
        $this->assertSame('default', $cuerpo['fallbacks']);
        $this->assertSame(['effort' => 'low'], $cuerpo['output_config']);
        $this->assertSame(['type' => 'ephemeral'], $cuerpo['system'][1]['cache_control']);
    }

    public function test_haiku_no_pide_respaldo(): void
    {
        $this->encender(['modelo' => 'claude-haiku-5-5']);
        $historial = [];
        $pila = HandlerStack::create(new MockHandler([new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'id' => 'msg_2', 'type' => 'message', 'role' => 'assistant', 'model' => 'claude-haiku-5-5', 'content' => [],
            'stop_reason' => 'refusal', 'stop_sequence' => null, 'stop_details' => null, 'context_management' => null, 'container' => null,
            'usage' => ['input_tokens' => 10, 'output_tokens' => 0],
        ]))]));
        $pila->push(Middleware::history($historial));

        $respuesta = (new ClaudeModelo(app(AsistenteConfig::class), new Guzzle(['handler' => $pila])))->responder([['type' => 'text', 'text' => 'Reglas']], [['role' => 'user', 'content' => 'Hola']]);

        $this->assertTrue($respuesta->rechazada);
        $this->assertArrayNotHasKey('fallbacks', json_decode((string) $historial[0]['request']->getBody(), true));
    }

    public function test_las_conversaciones_viejas_se_borran(): void
    {
        $vieja = ConversacionAsistente::query()->create(['uuid' => fake()->uuid()]);
        $vieja->mensajesLista()->create(['rol' => 'usuario', 'contenido' => 'Hola']);
        ConversacionAsistente::query()->whereKey($vieja->id)->update(['updated_at' => now()->subDays(91)]);
        ConversacionAsistente::query()->create(['uuid' => fake()->uuid()]);

        $this->artisan('asistente:depurar')->assertSuccessful();

        $this->assertSame(1, ConversacionAsistente::query()->count());
        $this->assertSame(0, MensajeAsistente::query()->count());
    }

    public function test_extrae_texto_de_txt_y_word(): void
    {
        $txt = tempnam(sys_get_temp_dir(), 'kb');
        file_put_contents($txt, "Rosa  creció en Itagüí.\r\n\r\n\r\n\r\nFin.");
        $this->assertSame("Rosa creció en Itagüí.\n\nFin.", ExtraerTexto::desde($txt, 'datos.txt'));

        $docx = tempnam(sys_get_temp_dir(), 'kb').'.docx';
        $zip = new \ZipArchive;
        $zip->open($docx, \ZipArchive::CREATE);
        $zip->addFromString('word/document.xml', '<w:document><w:body><w:p><w:r><w:t>Propuesta de salud</w:t></w:r></w:p><w:p><w:r><w:t>Segundo párrafo</w:t></w:r></w:p></w:body></w:document>');
        $zip->close();
        $this->assertSame("Propuesta de salud\nSegundo párrafo", ExtraerTexto::desde($docx));

        $this->expectException(RuntimeException::class);
        ExtraerTexto::desde($txt, 'foto.jpg');
    }

    private function usuario(string $rol): User
    {
        $usuario = User::query()->create(['name' => ucfirst($rol), 'email' => $rol.'@prueba.co', 'password' => 'ClaveSegura2026', 'activo' => true]);
        $usuario->assignRole($rol);

        return $usuario;
    }

    public function test_el_editor_maneja_la_base_de_conocimiento_pero_no_la_configuracion(): void
    {
        $this->actingAs($this->usuario('editor'));
        $this->get('/admin/asistente/conocimiento')->assertOk();
        $this->get('/admin/asistente/conocimiento/create')->assertOk();
        $this->get('/admin/configuracion-asistente')->assertForbidden();
        $this->get('/admin/asistente/conversaciones')->assertForbidden();
    }

    public function test_el_administrador_ve_la_configuracion_y_las_conversaciones(): void
    {
        $conversacion = ConversacionAsistente::query()->create(['uuid' => fake()->uuid()]);
        $conversacion->mensajesLista()->create(['rol' => 'usuario', 'contenido' => '¿Dónde estudió Rosa?']);

        $this->actingAs($this->usuario('administrador'));
        $this->get('/admin/configuracion-asistente')->assertOk()->assertSee('Falta la API key');
        $this->get('/admin/asistente/conversaciones')->assertOk()->assertSee('¿Dónde estudió Rosa?');
    }

    public function test_el_administrador_lee_una_conversacion(): void
    {
        $conversacion = ConversacionAsistente::query()->create(['uuid' => fake()->uuid()]);
        $conversacion->mensajesLista()->create(['rol' => 'usuario', 'contenido' => '¿Dónde estudió Rosa?']);
        $conversacion->mensajesLista()->create(['rol' => 'asistente', 'contenido' => 'Esa información todavía no la tengo.', 'estado' => 'ok']);

        $this->actingAs($this->usuario('administrador'));
        $this->get('/admin/asistente/conversaciones/'.$conversacion->id)->assertOk()
            ->assertSee('¿Dónde estudió Rosa?')->assertSee('Esa información todavía no la tengo.');
    }

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }
}
