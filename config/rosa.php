<?php

// Configuración propia del sitio. Lo que Comunicaciones cambia sin desarrollo vive en la tabla `ajustes`.
return [
    'celular_hmac_key' => env('CELULAR_HMAC_KEY'),

    // Doble factor obligatorio en el panel (RNF-05). Solo se apaga en local y en las pruebas automáticas.
    'dos_factores' => (bool) env('DOS_FACTORES', true),

    'crm' => [
        // «http» envía a {CRM_URL}/ingesta/{entidad} (sección 05, pendiente de confirmar con la
        // especificación OpenAPI del CRM). «nulo» deja el outbox pendiente sin enviar nada.
        'driver' => env('CRM_DRIVER', 'nulo'),
        'url' => env('CRM_URL'),
        'secreto' => env('CRM_SECRETO'),
        'timeout' => (int) env('CRM_TIMEOUT', 10),
        'lote' => (int) env('CRM_LOTE', 50),
        'alerta_email' => env('CRM_ALERTA_EMAIL'),
    ],

    'turnstile' => [
        'site_key' => env('TURNSTILE_SITE_KEY'),
        'secret' => env('TURNSTILE_SECRET'),
    ],

    'umami' => [
        'url' => env('UMAMI_URL'),
        'website_id' => env('UMAMI_WEBSITE_ID'),
        'dominios' => env('UMAMI_DOMINIOS', 'rosaacevedo.com'),
    ],

    // Vigencia del token de los pasos 2 y 3 del registro (sección 05).
    'token_registro_dias' => 7,

    // Antispam (RF-05).
    'limites' => [
        'por_ip' => 5,            // envíos
        'por_ip_minutos' => 10,
        'por_celular_dia' => 3,
    ],

    // Entorno de pruebas: contraseña básica y noindex (sección 06).
    'pruebas' => [
        'usuario' => env('PRUEBAS_USUARIO'),
        'clave' => env('PRUEBAS_CLAVE'),
    ],
];
