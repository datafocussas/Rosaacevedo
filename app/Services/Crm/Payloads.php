<?php

namespace App\Services\Crm;

use App\Models\AsistenciaEvento;
use App\Models\Ciudadano;
use App\Models\Interaccion;
use App\Models\PropuestaCiudadana;

/** Cuerpos que viajan al CRM (contrato mínimo de la sección 05). */
class Payloads
{
    public static function ciudadano(Ciudadano $ciudadano, string $evento): array
    {
        $ciudadano->loadMissing('barrio.comuna', 'barrio.comuna2007', 'voluntariado');
        $barrio = $ciudadano->barrio;

        return [
            'evento' => $evento,
            'uuid' => $ciudadano->uuid,
            'celular' => $ciudadano->celular(),
            'nombre' => $ciudadano->nombre,
            'email' => $ciudadano->email,
            'barrio' => $barrio ? [
                'codigo' => $barrio->codigo_externo,
                'nombre' => $barrio->nombre,
                'comuna_2024' => $barrio->comuna?->codigo,
                'comuna_2007' => $barrio->comuna2007?->codigo,
            ] : null,
            'paso' => $ciudadano->paso_alcanzado,
            'estado' => $ciudadano->estado,
            'es_voluntario' => $ciudadano->es_voluntario,
            'voluntariado' => $ciudadano->voluntariado ? [
                'intereses' => $ciudadano->voluntariado->intereses,
                'disponibilidad' => $ciudadano->voluntariado->disponibilidad,
                'puesto_votacion' => $ciudadano->voluntariado->puesto_votacion,
            ] : null,
            'consentimientos' => $ciudadano->consentimientos()->with('politica')->orderBy('created_at')->orderBy('id')->get()
                ->map(fn ($c) => [
                    'tipo' => $c->politica->tipo,
                    'version' => $c->politica->version,
                    'hash' => $c->politica->hash_sha256,
                    'otorgado' => $c->otorgado,
                    'fecha' => $c->created_at->format('Y-m-d\TH:i:s.vP'),
                    'formulario' => $c->formulario,
                ])->all(),
            'origen' => $ciudadano->primer_origen,
        ];
    }

    public static function interaccion(Interaccion $interaccion): array
    {
        return [
            'evento' => 'creado',
            'ciudadano_uuid' => $interaccion->ciudadano?->uuid,
            'tipo' => $interaccion->tipo,
            'fecha' => $interaccion->created_at?->format('Y-m-d\TH:i:sP'),
            'origen' => array_filter($interaccion->only(['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'codigo_q', 'pagina', 'variante', 'referrer'])),
            'comuna_pagina' => $interaccion->comunaPagina?->codigo,
        ];
    }

    public static function propuesta(PropuestaCiudadana $propuesta, string $evento): array
    {
        $propuesta->loadMissing('ciudadano', 'tema', 'temaConfirmado', 'barrio.comuna');

        return [
            'evento' => $evento,
            'codigo' => $propuesta->codigo,
            'ciudadano_uuid' => $propuesta->ciudadano->uuid,
            'tema' => $propuesta->tema?->nombre,
            'tema_confirmado' => $propuesta->temaConfirmado?->nombre,
            'barrio' => $propuesta->barrio?->codigo_externo,
            'comuna_2024' => $propuesta->barrio?->comuna?->codigo,
            'texto' => $propuesta->texto,
            'estado' => $propuesta->estado,
            'publicar_anonima' => $propuesta->publicar_anonima,
            'fecha' => $propuesta->created_at?->format('Y-m-d\TH:i:sP'),
        ];
    }

    public static function asistencia(AsistenciaEvento $asistencia, string $evento): array
    {
        $asistencia->loadMissing('ciudadano', 'evento');

        return [
            'evento' => $evento,
            'ciudadano_uuid' => $asistencia->ciudadano->uuid,
            'evento_slug' => $asistencia->evento->slug,
            'estado' => $asistencia->estado,
        ];
    }
}
