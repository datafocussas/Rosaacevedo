<?php

namespace App\Filament\Resources\CiudadanoResource\Pages;

use App\Contracts\CrmCliente;
use App\Filament\Resources\CiudadanoResource;
use App\Jobs\SincronizarCrm;
use App\Models\CrmOutbox;
use App\Services\Crm\CrmConexion;
use App\Services\Crm\Outbox;
use App\Services\Crm\Payloads;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class VerCiudadano extends ViewRecord
{
    protected static string $resource = CiudadanoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('enviar_crm')->label('Enviar al CRM ahora')->icon('heroicon-o-paper-airplane')->color('gray')
                ->visible(fn () => CiudadanoResource::verCompleto())
                ->disabled(fn () => ! CrmConexion::habilitada())
                ->tooltip(fn () => CrmConexion::habilitada() ? null : 'Activa la conexión en Sitio → Conexión con el CRM')
                ->action(function () {
                    // Reintenta ya lo que está en espera o con error; si todo estaba enviado, manda los datos actuales.
                    $filas = CrmOutbox::query()->where('entidad', 'ciudadano')->where('entidad_id', $this->record->id)
                        ->whereIn('estado', ['pendiente', 'error'])->orderBy('id')->get();
                    if ($filas->isEmpty()) {
                        $filas = collect([Outbox::registrar('ciudadano', $this->record->id, 'actualizado', Payloads::ciudadano($this->record->fresh(), 'actualizado'))]);
                    }

                    $sincronizar = new SincronizarCrm;
                    $filas->each(fn (CrmOutbox $fila) => $sincronizar->procesar(app(CrmCliente::class), $fila));

                    $this->record->refresh();
                    $ultima = $filas->last()->refresh();
                    Notification::make()
                        ->title($ultima->estado === 'enviado' ? 'Enviado al CRM' : 'No se pudo enviar')
                        ->body($ultima->estado === 'enviado' ? null : $ultima->ultimo_error.' Se reintentará automáticamente.')
                        ->{$ultima->estado === 'enviado' ? 'success' : 'danger'}()->send();
                }),
        ];
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);

        // RF-42: quién vio qué ficha y cuándo.
        activity('registros')->causedBy(auth()->user())->performedOn($this->record)
            ->withProperties(['completo' => CiudadanoResource::verCompleto(), 'ip' => request()->ip()])
            ->log('Vio la ficha del ciudadano');
    }
}
