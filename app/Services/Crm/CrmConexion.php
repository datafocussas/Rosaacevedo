<?php

namespace App\Services\Crm;

use App\Models\Ajuste;
use App\Models\Voluntariado;
use App\Services\Ajustes;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;

/**
 * Conexión con el CRM configurada desde el panel (Sitio → Conexión con el CRM): URL por tipo de dato,
 * autenticación con API key y qué campos del sitio viajan con qué nombre. La API key se guarda cifrada
 * con la APP_KEY y nunca se escribe en la bitácora. Si la conexión del panel no está activa, se usa la
 * del .env (CRM_DRIVER=http), como antes.
 */
class CrmConexion
{
    public const CLAVE = 'crm_conexion';

    public const CLAVE_API_KEY = 'crm_api_key';

    public const AUTENTICACION = [
        'bearer' => 'Authorization: Bearer {API key}',
        'header' => 'Encabezado propio (por ejemplo X-API-Key)',
        'ninguna' => 'Sin autenticación',
    ];

    /** Campos que el sitio recoge y puede enviar, por tipo de dato. */
    public const CAMPOS = [
        'ciudadano' => [
            'uuid' => 'Identificador del registro en el sitio (uuid)',
            'codigo' => 'Código de atribución (R-XXXXXX)',
            'nombre' => 'Nombre',
            'celular' => 'Celular (+57…)',
            'email' => 'Correo',
            'comuna' => 'Comuna (Comuna 1…7 o Corregimiento El Manzanillo)',
            'comuna_codigo' => 'Código de la comuna (C01…C07, CORR)',
            'comuna_2007' => 'Comuna de la división 2007 (JAL)',
            'barrio' => 'Barrio o vereda',
            'barrio_codigo' => 'Código del barrio en el CRM',
            'paso' => 'Paso alcanzado en el registro (1 a 3)',
            'es_voluntario' => 'Quiere ser voluntario (sí/no)',
            'intereses' => '¿Cómo quiere ayudar?',
            'disponibilidad' => 'Disponibilidad',
            'puesto_votacion' => 'Puesto de votación',
            'autoriza_datos' => 'Autorización de tratamiento de datos',
            'autoriza_whatsapp' => 'Autoriza mensajes por WhatsApp',
            'autoriza_afinidad' => 'Autoriza dato de afinidad política',
            'estado' => 'Estado (activo, retirado…)',
            'evento' => 'Evento (creado, actualizado, revocado)',
            'utm_source' => 'Origen: fuente (utm_source)',
            'utm_medium' => 'Origen: medio (utm_medium)',
            'utm_campaign' => 'Origen: campaña (utm_campaign)',
            'codigo_q' => 'Origen: código QR',
            'pagina_origen' => 'Origen: página del primer contacto',
        ],
        'propuesta' => [
            'codigo' => 'Código de la propuesta (PR-AAAA-NNNN)',
            'ciudadano_uuid' => 'Identificador del ciudadano (uuid)',
            'tema' => 'Tema elegido',
            'tema_confirmado' => 'Tema confirmado por el moderador',
            'comuna_codigo' => 'Código de la comuna',
            'barrio_codigo' => 'Código del barrio en el CRM',
            'texto' => 'Texto de la propuesta',
            'estado' => 'Estado de la propuesta',
            'publicar_anonima' => 'Autoriza publicarla sin su nombre',
            'fecha' => 'Fecha de envío',
        ],
    ];

    public const ENTIDADES = ['ciudadano' => 'Registros (leads)', 'propuesta' => 'Propuestas del buzón'];

    public function __construct(private Ajustes $ajustes) {}

    public function config(): array
    {
        $guardado = (array) ($this->ajustes->get(self::CLAVE) ?? []);

        return array_replace([
            'activo' => false,
            'url_ciudadano' => null,
            'url_propuesta' => null,
            'autenticacion' => 'bearer',
            'encabezado' => 'X-API-Key',
            'campos_ciudadano' => [],
            'campos_propuesta' => [],
        ], $guardado);
    }

    public function apiKey(): ?string
    {
        $cifrada = $this->ajustes->get(self::CLAVE_API_KEY);

        try {
            return $cifrada ? Crypt::decryptString($cifrada) : null;
        } catch (\Throwable) {
            return null; // Cambió la APP_KEY: hay que volver a escribirla.
        }
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
            'grupo' => 'crm',
            'updated_by' => auth()->id(),
        ]);

        activity('configuracion')->causedBy(auth()->user())
            ->withProperties(['clave' => self::CLAVE_API_KEY])
            ->log(filled($valor) ? 'Cambió la API key del CRM' : 'Borró la API key del CRM');

        $this->ajustes->olvidar();
    }

    /** La conexión del panel está lista para enviar. */
    public function activa(): bool
    {
        $config = $this->config();

        return (bool) $config['activo'] && filled($config['url_ciudadano'])
            && ($config['autenticacion'] === 'ninguna' || $this->tieneApiKey());
    }

    /** Hay algún canal de envío: el del panel o el del .env. */
    public static function habilitada(): bool
    {
        return app(self::class)->activa() || config('rosa.crm.driver') === 'http';
    }

    public function url(string $entidad): ?string
    {
        return $this->config()['url_'.$entidad] ?? null;
    }

    /** Tipos de dato que esta conexión envía (los demás esperan en el outbox). */
    public function entidades(): array
    {
        return array_values(array_filter(array_keys(self::ENTIDADES), fn ($e) => filled($this->url($e))));
    }

    public function encabezados(): array
    {
        $config = $this->config();
        $llave = $this->apiKey();

        return match ($config['autenticacion']) {
            'bearer' => $llave ? ['Authorization' => 'Bearer '.$llave] : [],
            'header' => $llave ? [($config['encabezado'] ?: 'X-API-Key') => $llave] : [],
            default => [],
        };
    }

    /**
     * Cuerpo que viaja al CRM. Sin campos configurados se envía el registro completo; con campos, solo
     * esos y con el nombre que espera el CRM (admite puntos para anidar: «contacto.nombre»).
     */
    public function cuerpo(string $entidad, array $payload): array
    {
        $mapa = collect($this->config()['campos_'.$entidad] ?? [])
            ->filter(fn ($fila) => filled($fila['sitio'] ?? null) && filled($fila['crm'] ?? null));

        if ($mapa->isEmpty()) {
            return $payload;
        }

        $cuerpo = [];
        foreach ($mapa as $fila) {
            Arr::set($cuerpo, trim($fila['crm']), self::valor($entidad, $payload, $fila['sitio']));
        }

        return $cuerpo;
    }

    /** Valor plano de un campo del sitio a partir del payload guardado en el outbox. */
    public static function valor(string $entidad, array $payload, string $campo): mixed
    {
        if ($entidad === 'propuesta') {
            return match ($campo) {
                'barrio_codigo' => $payload['barrio'] ?? null,
                'comuna_codigo' => $payload['comuna_2024'] ?? null,
                default => $payload[$campo] ?? null,
            };
        }

        $consentimiento = function (string $tipo) use ($payload): bool {
            $ultimo = collect($payload['consentimientos'] ?? [])->where('tipo', $tipo)->last();

            return (bool) ($ultimo['otorgado'] ?? false);
        };

        return match ($campo) {
            'comuna' => $payload['comuna']['nombre'] ?? null,
            'comuna_codigo' => $payload['comuna']['codigo'] ?? ($payload['barrio']['comuna_2024'] ?? null),
            'comuna_2007' => $payload['barrio']['comuna_2007'] ?? null,
            'barrio' => $payload['barrio']['nombre'] ?? null,
            'barrio_codigo' => $payload['barrio']['codigo'] ?? null,
            'intereses' => collect($payload['voluntariado']['intereses'] ?? [])->map(fn ($i) => Voluntariado::INTERESES[$i] ?? $i)->join(', ') ?: null,
            'disponibilidad' => collect($payload['voluntariado']['disponibilidad'] ?? [])->map(fn ($d) => Voluntariado::DISPONIBILIDAD[$d] ?? $d)->join(', ') ?: null,
            'puesto_votacion' => $payload['voluntariado']['puesto_votacion'] ?? null,
            'autoriza_datos' => $consentimiento('autorizacion_general'),
            'autoriza_whatsapp' => $consentimiento('whatsapp'),
            'autoriza_afinidad' => $consentimiento('afinidad_politica'),
            'utm_source', 'utm_medium', 'utm_campaign', 'codigo_q' => $payload['origen'][$campo] ?? null,
            'pagina_origen' => $payload['origen']['pagina'] ?? null,
            default => $payload[$campo] ?? null,
        };
    }
}
