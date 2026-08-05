<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class DemandeStatutNotification extends Notification
{
    public function __construct(
        protected string $message,
        protected int $demandeId,
        protected string $statut,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): DatabaseMessage
    {
        return new DatabaseMessage([
            'title' => 'Statut de votre demande',
            'message' => $this->message,
            'demande_id' => $this->demandeId,
            'statut' => $this->statut,
        ]);
    }
}
