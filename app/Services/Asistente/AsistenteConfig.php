<?php

namespace App\Services\Asistente;

use App\Models\Ajuste;
use App\Services\Ajustes;
use Illuminate\Support\Facades\Crypt;

/**
 * Configuración del asistente (Asistente → Configuración): encendido, modelo, textos del chat, límites y retención.
 * La API key de Anthropic se guarda cifrada con la APP_KEY y nunca se escribe en la bitácora; si no hay una en el
 * panel, se usa ANTHROPIC_API_KEY del .env.
 */
class AsistenteConfig
{
    public const CLAVE = 'asistente';

    public const CLAVE_API_KEY = 'asistente_api_key';

    /** Modelos de Claude que se pueden elegir. El primero es el recomendado. */
    public const MODELOS = [
        'claude-opus-5-5' => 'Claude Opus 5.5 (recomendado: mejores respuestas)',
        'claude-sonnet-5-5' => 'Claude Sonnet 5.5 (más económico)',
        'claude-haiku-5-5' => 'Claude Haiku 5.5 (el más económico y rápido)',
    ];

    public const ESFUERZOS = [
        'low' => 'Bajo (respuestas rápidas; suficiente para conversar)',
        'medium' => 'Medio',
        'high' => 'Alto (piensa más antes de responder; más lento y costoso)',
    ];

    public const DEFECTO = [
        'activo' => false,
        'nombre' => 'Asistente de Rosa',
        'bienvenida' => 'Hola. Soy el asistente virtual de la campaña de Rosa Acevedo. Pregúntame por su trayectoria, sus propuestas o su visión de Itagüí.',
        'sugerencias' => ['¿Quién es Rosa Acevedo?', '¿Qué propone para la seguridad?', '¿Cómo me sumo a la campaña?'],
        'instrucciones' => '',
        'modelo' => 'claude-opus-5-5',
        'esfuerzo' => 'low',
        'incluir_ejes' => true,
        'max_por_conversacion' => 30,
        'max_diario' => 2000,
        'dias_retencion' => 90,
    ];

    public function __construct(private Ajustes $ajustes) {}

    public function todo(): array
    {
        $guardado = (array) ($this->ajustes->get(self::CLAVE) ?? []);
        $config = array_replace(self::DEFECTO, $guardado);
        $config['sugerencias'] = array_values(array_filter((array) $config['sugerencias'], 'filled'));

        if (! array_key_exists($config['modelo'], self::MODELOS)) {
            $config['modelo'] = self::DEFECTO['modelo'];
        }

        return $config;
    }

    public function get(string $clave): mixed
    {
        return $this->todo()[$clave] ?? null;
    }

    public function apiKey(): ?string
    {
        $cifrada = $this->ajustes->get(self::CLAVE_API_KEY);

        try {
            $valor = $cifrada ? Crypt::decryptString($cifrada) : null;
        } catch (\Throwable) {
            $valor = null; // Cambió la APP_KEY: hay que volver a escribirla.
        }

        return filled($valor) ? $valor : (config('rosa.asistente.api_key') ?: null);
    }

    public function tieneApiKey(): bool
    {
        return filled($this->apiKey());
    }

    /** Guarda la API key cifrada. En la bitácora solo queda que cambió, nunca el valor. */
    public function guardarApiKey(?string $valor): void
    {
        Ajuste::query()->updateOrCreate(['clave' => self::CLAVE_API_KEY], [
            'valor' => filled($valor) ? Crypt::encryptString(trim($valor)) : null,
            'grupo' => 'asistente',
            'updated_by' => auth()->id(),
        ]);

        activity('configuracion')->causedBy(auth()->user())
            ->withProperties(['clave' => self::CLAVE_API_KEY])
            ->log(filled($valor) ? 'Cambió la API key del asistente' : 'Borró la API key del asistente');

        $this->ajustes->olvidar();
    }

    /** Encendido en el panel y con API key: el sitio muestra el botón del asistente. */
    public function disponible(): bool
    {
        return (bool) $this->get('activo') && $this->tieneApiKey();
    }
}
