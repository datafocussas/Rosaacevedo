<?php

namespace App\Services;

use App\Models\Politica;
use App\Models\SolicitudTitular;
use App\Services\Crm\Outbox;
use App\Services\Crm\Payloads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Aplica una revocatoria o supresión verificada: filas de revocatoria (solo inserción), estado «retirado» y aviso al CRM. */
class Titular
{
    public function __construct(private Consentimientos $consentimientos) {}

    public function retirar(SolicitudTitular $solicitud, Request $request): void
    {
        $ciudadano = $solicitud->ciudadano;
        abort_unless($ciudadano, 422);

        DB::transaction(function () use ($ciudadano, $solicitud, $request) {
            foreach ($ciudadano->consentimientosVigentes() as $tipo => $otorgado) {
                if ($otorgado && Politica::vigente($tipo)) {
                    $this->consentimientos->registrar($ciudadano, $tipo, false, 'titular', $request);
                }
            }

            $ciudadano->update(['estado' => 'retirado', 'crm_sync_estado' => 'pendiente']);
            Outbox::registrar('ciudadano', $ciudadano->id, 'revocado', Payloads::ciudadano($ciudadano->fresh(), 'revocado'));

            $solicitud->update(['estado' => 'en_tramite', 'atendida_por' => auth()->id()]);
        });

        activity('titular')->causedBy(auth()->user())->performedOn($solicitud)->log('Aplicó retiro de autorizaciones');
    }
}
