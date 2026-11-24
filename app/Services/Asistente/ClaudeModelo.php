<?php

namespace App\Services\Asistente;

use Anthropic\Beta\Messages\BetaTextBlock;
use Anthropic\Client;
use App\Contracts\ModeloAsistente;
use GuzzleHttp\Client as Guzzle;
use Psr\Http\Client\ClientInterface;
use RuntimeException;

/**
 * Claude (Anthropic) con el SDK oficial de PHP. El modelo y el esfuerzo salen del panel. Con Opus y Sonnet se piden
 * los respaldos automáticos del servidor: si el modelo declina una pregunta por política, otro modelo la intenta.
 */
class ClaudeModelo implements ModeloAsistente
{
    /** Respuestas cortas de chat; deja margen para el razonamiento interno del modelo. */
    private const MAX_TOKENS = 4096;

    private const CON_RESPALDO = ['claude-opus-5-5', 'claude-sonnet-5-5'];

    /** @param  ClientInterface|null  $transporte  Cliente HTTP; las pruebas pasan uno simulado. */
    public function __construct(private AsistenteConfig $config, private ?ClientInterface $transporte = null) {}

    public function responder(array $sistema, array $mensajes): RespuestaModelo
    {
        $apiKey = $this->config->apiKey() ?? throw new RuntimeException('Falta la API key de Anthropic (Asistente → Configuración).');
        $modelo = (string) $this->config->get('modelo');
        $segundos = (float) config('rosa.asistente.timeout', 60);

        $cliente = new Client(apiKey: $apiKey, requestOptions: [
            'maxRetries' => 2,
            'timeout' => $segundos,
            'transporter' => $this->transporte ?? new Guzzle(['timeout' => $segundos, 'connect_timeout' => 10]),
        ]);

        $respaldo = in_array($modelo, self::CON_RESPALDO, true);

        $respuesta = $cliente->beta->messages->create(
            maxTokens: self::MAX_TOKENS,
            messages: $mensajes,
            model: $modelo,
            system: $sistema,
            outputConfig: ['effort' => (string) $this->config->get('esfuerzo')],
            fallbacks: $respaldo ? 'default' : null,
            betas: $respaldo ? ['server-side-fallback-2026-07-01'] : null,
        );

        $texto = '';
        foreach ($respuesta->content as $bloque) {
            if ($bloque instanceof BetaTextBlock) {
                $texto .= $bloque->text;
            }
        }

        return new RespuestaModelo(
            texto: $texto,
            tokensEntrada: $respuesta->usage->inputTokens + (int) $respuesta->usage->cacheReadInputTokens + (int) $respuesta->usage->cacheCreationInputTokens,
            tokensSalida: $respuesta->usage->outputTokens,
            rechazada: $respuesta->stopReason === 'refusal',
        );
    }
}
