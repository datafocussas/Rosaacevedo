<?php

namespace App\Http\Controllers;

use App\Http\Requests\SolicitudTitularRequest;
use App\Models\Ciudadano;
use App\Models\SolicitudTitular;
use App\Support\Celular;
use Illuminate\Support\Facades\DB;

/**
 * Derechos del titular (RF-44, sección 07). La solicitud queda radicada con su plazo legal; la
 * revocatoria o supresión se aplica desde el panel después de verificar la identidad, para que
 * nadie pueda retirar los datos de otra persona con solo escribir su número.
 */
class TitularController extends Controller
{
    public function index()
    {
        return view('sitio.mis-datos', ['tipos' => SolicitudTitular::TIPOS]);
    }

    public function store(SolicitudTitularRequest $request)
    {
        $contacto = trim(strip_tags($request->input('contacto')));
        $e164 = Celular::normalizar($contacto);

        $solicitud = DB::transaction(function () use ($request, $contacto, $e164) {
            return SolicitudTitular::query()->create([
                'radicado' => SolicitudTitular::siguienteRadicado(),
                'ciudadano_id' => $e164 ? Ciudadano::buscarPorCelular($e164)?->id : null,
                'tipo' => $request->input('tipo'),
                'nombre' => trim(strip_tags($request->input('nombre'))),
                // Si es un celular se guarda enmascarado: la ficha vinculada tiene el dato completo.
                'contacto' => $e164 ? Celular::enmascarar($e164) : mb_strtolower($contacto),
                'detalle' => $request->filled('detalle') ? trim(strip_tags($request->input('detalle'))) : null,
                'vence_en' => SolicitudTitular::calcularVencimiento($request->input('tipo'))->toDateString(),
            ]);
        });

        if ($request->expectsJson()) {
            return response()->json(['radicado' => $solicitud->radicado, 'vence_en' => $solicitud->vence_en->toDateString()], 201);
        }

        return redirect()->route('mis-datos')->with('estado', [
            'radicado' => $solicitud->radicado,
            'vence_en' => $solicitud->vence_en->translatedFormat('j \d\e F \d\e Y'),
        ]);
    }
}
