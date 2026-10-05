<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegistroCompletarRequest;
use App\Http\Requests\RegistroPaso1Request;
use App\Models\TerritorioBarrio;
use App\Services\Ajustes;
use App\Services\RegistroCiudadano;
use App\Support\Origen;
use App\Support\TokenRegistro;
use Illuminate\Http\Request;

/**
 * Súmate (RF-01, RF-06). Responde JSON para la API (/api/v1/registro) y HTML clásico para el
 * formulario sin JavaScript; en HTML el token viaja en la sesión, nunca en la URL.
 */
class RegistroController extends Controller
{
    public function __construct(private RegistroCiudadano $registro, private Ajustes $ajustes) {}

    public function index()
    {
        return view('sitio.sumate');
    }

    public function store(RegistroPaso1Request $request)
    {
        $resultado = $this->registro->paso1($request->datos(), Origen::desdePeticion($request), $request);
        $whatsapp = $this->ajustes->enlaceWhatsapp($resultado['codigo']);

        if ($request->expectsJson()) {
            // Mismo código y formato exista o no el celular: no se revela si ya estaba registrado.
            return response()->json([
                'token' => $resultado['token'],
                'codigo' => $resultado['codigo'],
                'paso' => 1,
                'whatsapp' => $whatsapp,
            ], 201);
        }

        $request->session()->put('registro', ['token' => $resultado['token'], 'codigo' => $resultado['codigo'], 'whatsapp' => $whatsapp]);

        return redirect()->route('sumate.continuar');
    }

    public function continuar(Request $request)
    {
        $token = $request->session()->get('registro.token');
        $datos = $token ? TokenRegistro::leer($token) : null;

        if (! $datos) {
            return redirect()->route('sumate.gracias');
        }

        return view('sitio.sumate-continuar', [
            'paso' => (int) $datos['p'],
            'barrios' => self::barriosAgrupados(),
        ]);
    }

    public function completar(RegistroCompletarRequest $request)
    {
        $resultado = $this->registro->completar($request->input('token'), $request->datos(), Origen::desdePeticion($request), $request);

        if ($request->expectsJson()) {
            return response()->json(['paso' => $resultado['paso'], 'token' => $resultado['token']]);
        }

        if ($resultado['token']) {
            $request->session()->put('registro.token', $resultado['token']);

            return redirect()->route('sumate.continuar');
        }

        $request->session()->forget('registro.token');

        return redirect()->route('sumate.gracias');
    }

    public function gracias(Request $request)
    {
        $registro = $request->session()->get('registro');

        if (! $registro) {
            return redirect()->route('sumate');
        }

        $request->session()->forget('registro.token');

        return view('sitio.sumate-gracias', ['codigo' => $registro['codigo'], 'whatsapp' => $registro['whatsapp']]);
    }

    /** Barrios activos agrupados por comuna 2024 para los selectores. */
    public static function barriosAgrupados()
    {
        return TerritorioBarrio::query()->with('comuna')->where('activo', true)
            ->get()
            ->sortBy([fn ($b) => $b->comuna->codigo === 'CORR' ? 'Z' : $b->comuna->codigo, 'nombre'])
            ->groupBy(fn ($b) => $b->comuna->esCorregimiento() ? 'Corregimiento El Manzanillo' : $b->comuna->nombre);
    }
}
