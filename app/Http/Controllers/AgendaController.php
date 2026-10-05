<?php

namespace App\Http\Controllers;

use App\Http\Requests\AsistenciaRequest;
use App\Models\AsistenciaEvento;
use App\Models\Evento;
use App\Services\Consentimientos;
use App\Services\Crm\Outbox;
use App\Services\Crm\Payloads;
use App\Services\RegistroCiudadano;
use App\Support\Celular;
use App\Support\Origen;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Agenda con inscripción y archivo .ics (RF-15). */
class AgendaController extends Controller
{
    public function index()
    {
        return view('sitio.agenda', [
            'proximos' => Evento::query()->proximos()->with('comuna')->get(),
            'pasados' => Evento::query()->whereIn('estado', ['publicado', 'realizado'])->where('publico', true)
                ->where('inicia_en', '<', now()->startOfDay())->orderByDesc('inicia_en')->with('comuna')->limit(12)->get(),
        ]);
    }

    public function show(Evento $evento)
    {
        abort_unless(in_array($evento->estado, ['publicado', 'realizado', 'cancelado'], true) && $evento->publico, 404);

        $inscritos = $evento->asistencias()->where('estado', '!=', 'cancelo')->count();

        return view('sitio.evento', [
            'evento' => $evento->load('comuna'),
            'lleno' => $evento->cupo !== null && $inscritos >= $evento->cupo,
        ]);
    }

    public function ics(Evento $evento)
    {
        abort_unless($evento->estado === 'publicado' && $evento->publico, 404);

        $formato = fn ($fecha) => $fecha->clone()->utc()->format('Ymd\THis\Z');
        $escapar = fn ($texto) => str_replace(['\\', ';', ',', "\n"], ['\\\\', '\;', '\,', '\n'], (string) $texto);
        $fin = $evento->termina_en ?? $evento->inicia_en->clone()->addHours(2);

        $contenido = implode("\r\n", [
            'BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//rosaacevedo.com//Agenda//ES', 'CALSCALE:GREGORIAN', 'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:evento-'.$evento->id.'@rosaacevedo.com',
            'DTSTAMP:'.$formato(now()),
            'DTSTART:'.$formato($evento->inicia_en),
            'DTEND:'.$formato($fin),
            'SUMMARY:'.$escapar($evento->titulo),
            'LOCATION:'.$escapar(trim($evento->lugar.' '.$evento->direccion)),
            'DESCRIPTION:'.$escapar(strip_tags((string) $evento->descripcion)."\n".route('agenda.show', $evento)),
            'URL:'.route('agenda.show', $evento),
            'END:VEVENT', 'END:VCALENDAR', '',
        ]);

        return response($contenido, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$evento->slug.'.ics"',
        ]);
    }

    public function asistencia(AsistenciaRequest $request, Evento $evento, RegistroCiudadano $registro, Consentimientos $consentimientos)
    {
        abort_unless($evento->estado === 'publicado' && $evento->publico, 404);

        $e164 = Celular::normalizar($request->input('celular'));
        $registro->limitarPorCelular($e164);
        $origen = Origen::desdePeticion($request);

        DB::transaction(function () use ($request, $evento, $registro, $consentimientos, $e164, $origen) {
            $inscritos = $evento->asistencias()->where('estado', '!=', 'cancelo')->lockForUpdate()->count();
            if ($evento->cupo !== null && $inscritos >= $evento->cupo) {
                throw ValidationException::withMessages(['celular' => 'El cupo de este encuentro ya se llenó.']);
            }

            [$ciudadano] = $registro->encontrarOCrear($e164, trim(strip_tags($request->input('nombre'))), $origen);
            $consentimientos->registrar($ciudadano, 'autorizacion_general', true, 'evento', $request);
            if ($request->boolean('consent.whatsapp')) {
                $consentimientos->registrar($ciudadano, 'whatsapp', true, 'evento', $request);
            }

            $asistencia = AsistenciaEvento::query()->where('evento_id', $evento->id)->where('ciudadano_id', $ciudadano->id)->first();
            if ($asistencia) {
                AsistenciaEvento::query()->where('evento_id', $evento->id)->where('ciudadano_id', $ciudadano->id)->update(['estado' => 'inscrito', 'updated_at' => now()]);
            } else {
                AsistenciaEvento::query()->create(['evento_id' => $evento->id, 'ciudadano_id' => $ciudadano->id, 'estado' => 'inscrito']);
            }

            Outbox::registrar('ciudadano', $ciudadano->id, 'actualizado', Payloads::ciudadano($ciudadano->fresh(), 'actualizado'));
            $registro->interaccion($ciudadano, 'asistencia', $origen);
            $asistencia = AsistenciaEvento::query()->where('evento_id', $evento->id)->where('ciudadano_id', $ciudadano->id)->first();
            Outbox::registrar('asistencia', $ciudadano->id, 'creado', Payloads::asistencia($asistencia, 'creado'));
        });

        if ($request->expectsJson()) {
            return response()->json(null, 201);
        }

        return redirect()->route('agenda.show', $evento)->with('estado', 'Te inscribimos. Te esperamos.');
    }
}
