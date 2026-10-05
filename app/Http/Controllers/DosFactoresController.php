<?php

namespace App\Http\Controllers;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use PragmaRX\Google2FA\Google2FA;

/** Doble factor obligatorio en el panel (RNF-05): TOTP compatible con Google Authenticator, Authy, etc. */
class DosFactoresController extends Controller
{
    public const SESION = 'dos_factores_ok';

    public function mostrar(Request $request, Google2FA $google2fa)
    {
        $usuario = $request->user();

        if ($request->session()->get(self::SESION) === $usuario->id) {
            return redirect('/admin');
        }

        $qr = null;
        $secreto = null;

        if (! $usuario->tieneDosFactores()) {
            $secreto = $request->session()->get('dos_factores_nuevo') ?? $google2fa->generateSecretKey(32);
            $request->session()->put('dos_factores_nuevo', $secreto);
            $uri = $google2fa->getQRCodeUrl('Rosa Acevedo · Panel', $usuario->email, $secreto);
            $qr = (new Writer(new ImageRenderer(new RendererStyle(220, 1), new SvgImageBackEnd)))->writeString($uri);
        }

        return view('admin.doble-factor', ['qr' => $qr, 'secreto' => $secreto, 'configurando' => $qr !== null]);
    }

    public function verificar(Request $request, Google2FA $google2fa)
    {
        $request->validate(['codigo' => ['required', 'digits:6']], ['codigo.required' => 'Escribe el código de 6 dígitos.', 'codigo.digits' => 'El código tiene 6 dígitos.']);

        $usuario = $request->user();
        $configurando = ! $usuario->tieneDosFactores();
        $secreto = $configurando ? $request->session()->get('dos_factores_nuevo') : $usuario->dos_factores_secreto;

        if (! $secreto || ! $google2fa->verifyKey($secreto, $request->input('codigo'), 1)) {
            activity('seguridad')->causedBy($usuario)->log('Código de doble factor incorrecto');

            return back()->withErrors(['codigo' => 'El código no es válido. Revisa la hora de tu celular e inténtalo de nuevo.']);
        }

        if ($configurando) {
            $usuario->forceFill(['dos_factores_secreto' => $secreto, 'dos_factores_confirmado_en' => now()])->save();
            $request->session()->forget('dos_factores_nuevo');
            activity('seguridad')->causedBy($usuario)->log('Doble factor activado');
        }

        $request->session()->regenerate();
        $request->session()->put(self::SESION, $usuario->id);

        return redirect()->intended('/admin');
    }
}
