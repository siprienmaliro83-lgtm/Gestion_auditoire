<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\DatabaseMessage; 
use Illuminate\Notifications\Notification;

class ProgrammationAttribueeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected string $message,
        protected int $demandeId,
        protected int $programmationId,
        protected ?string $date = null,
        protected ?string $heureDebut = null,
        protected ?string $heureFin = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Nouvelle programmation attribuée',
            'message' => $this->message,
            'demande_id' => $this->demandeId,
            'programmation_id' => $this->programmationId,
            'created_at' => now()->toDateTimeString(),
            'status' => 'non_lue',
            'date' => $this->date,
            'heure_debut' => $this->heureDebut,
            'heure_fin' => $this->heureFin,
        ];
    }
}
