<?php

namespace App\Http\Controllers;

use App\Http\Requests\PropuestaRequest;
use App\Models\PropuestaCiudadana;
use App\Models\Tema;
use App\Models\TerritorioBarrio;
use App\Services\Consentimientos;
use App\Services\Crm\Outbox;
use App\Services\Crm\Payloads;
use App\Services\RegistroCiudadano;
use App\Support\Celular;
use App\Support\Origen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Buzón ciudadano (RF-20): código PR-AAAA-NNNN, mismo ciudadano deduplicado por celular. */
class BuzonController extends Controller
{
    public function index(Request $request)
    {
        return view('sitio.buzon', [
            'temas' => Tema::query()->where('activo', true)->orderBy('orden')->get(),
            'barrios' => RegistroController::barriosAgrupados(),
            'comunaId' => $request->integer('comuna') ?: null,
            'temaId' => $request->integer('tema') ?: null,
        ]);
    }

    public function store(PropuestaRequest $request, RegistroCiudadano $registro, Consentimientos $consentimientos)
    {
        $e164 = Celular::normalizar($request->input('celular'));
        $registro->limitarPorCelular($e164);
        $origen = Origen::desdePeticion($request);

        $propuesta = DB::transaction(function () use ($request, $registro, $consentimientos, $e164, $origen) {
            [$ciudadano] = $registro->encontrarOCrear($e164, trim(strip_tags($request->input('nombre'))), $origen);
            $consentimientos->registrar($ciudadano, 'autorizacion_general', true, 'buzon', $request);
            if ($request->boolean('consent.publicar')) {
                $consentimientos->registrar($ciudadano, 'publicar_propuesta', true, 'buzon', $request);
            }

            $barrio = $request->integer('barrio_id') ? TerritorioBarrio::query()->find($request->integer('barrio_id')) : null;
            $comunaId = $barrio?->comuna_2024_id ?? ($request->integer('comuna_id') ?: null);

            $propuesta = PropuestaCiudadana::query()->create([
                'codigo' => PropuestaCiudadana::siguienteCodigo(),
                'ciudadano_id' => $ciudadano->id,
                'tema_id' => $request->integer('tema_id'),
                'barrio_id' => $barrio?->id,
                'comuna_id' => $comunaId,
                'texto' => trim(strip_tags($request->input('texto'))),
                'publicar_anonima' => $request->boolean('consent.publicar'),
            ]);

            if (! $ciudadano->barrio_id && $propuesta->barrio_id) {
                $ciudadano->update(['barrio_id' => $propuesta->barrio_id]);
            }
            if (! $ciudadano->comuna_id && $propuesta->comuna_id) {
                $ciudadano->update(['comuna_id' => $propuesta->comuna_id]);
            }

            Outbox::registrar('ciudadano', $ciudadano->id, 'actualizado', Payloads::ciudadano($ciudadano->fresh(), 'actualizado'));
            $registro->interaccion($ciudadano, 'propuesta', $origen);
            Outbox::registrar('propuesta', $propuesta->id, 'creado', Payloads::propuesta($propuesta, 'creado'));

            return $propuesta;
        });

        if ($request->hasFile('foto')) {
            // Disco privado: la foto solo se ve desde el panel.
            $propuesta->addMediaFromRequest('foto')->usingFileName($propuesta->codigo.'.'.$request->file('foto')->extension())->toMediaCollection('foto');
        }

        if ($request->expectsJson()) {
            return response()->json(['codigo' => $propuesta->codigo], 201);
        }

        return redirect()->route('buzon.gracias')->with('propuesta', $propuesta->codigo);
    }

    public function gracias(Request $request)
    {
        $codigo = $request->session()->get('propuesta');

        return $codigo ? view('sitio.buzon-gracias', ['codigo' => $codigo]) : redirect()->route('buzon');
    }
}
