<?php

namespace App\Notifications;

use App\Models\CrmOutbox;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FalloSincronizacionCrm extends Notification
{
    public function __construct(public CrmOutbox $fila) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // Sin datos personales en el correo: solo la referencia del outbox.
        return (new MailMessage)
            ->subject('El CRM no recibe datos del sitio (5 intentos fallidos)')
            ->line("La fila {$this->fila->id} del outbox ({$this->fila->entidad}, {$this->fila->evento}) falló cinco veces.")
            ->line('Último error: '.$this->fila->ultimo_error)
            ->line('El sitio sigue captando; los envíos se reintentan cada 6 horas.')
            ->action('Ver en el panel', url('/admin'));
    }
}
