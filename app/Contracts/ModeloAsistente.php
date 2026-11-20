<?php

namespace App\Contracts;

use App\Services\Asistente\RespuestaModelo;

/**
 * Modelo de lenguaje que responde en el asistente del sitio. En producción, Claude (App\Services\Asistente\ClaudeModelo);
 * en las pruebas, un doble que no sale a internet.
 */
interface ModeloAsistente
{
    /**
     * @param  list<array{type: string, text: string, cacheControl?: array}>  $sistema  Instrucciones y base de conocimiento
     * @param  list<array{role: 'user'|'assistant', content: string}>  $mensajes  Conversación, terminando en la pregunta
     */
    public function responder(array $sistema, array $mensajes): RespuestaModelo;
}
