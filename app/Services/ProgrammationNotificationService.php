<?php

namespace App\Services;

use App\Models\DemandeAuditoire;
use App\Models\Enseignant;
use App\Models\User;
use App\Notifications\DemandeStatutNotification;
use App\Notifications\ProgrammationAttribueeNotification;
use Illuminate\Support\Collection;

class ProgrammationNotificationService
{
    /**
     * Notifie l'utilisateur propriétaire de la demande du nouveau statut
     * (ex. refus ou acceptation) — Notification Laravel en base de données.
     */
    public function notifyStatut(DemandeAuditoire $demande, string $statut): void
    {
        $owner = $demande->user;
        if (! $owner) {
            return;
        }

        $message = sprintf(
            'Votre demande de cours de %s est passée au statut "%s".',
            $demande->ec?->nom ?? 'cet EC',
            $statut,
        );

        if ($statut === 'Refusée' && $demande->motif_refus) {
            $message .= ' Motif : '.$demande->motif_refus;
        }

        $owner->notify(new DemandeStatutNotification($message, $demande->id, $statut));
    }

    public function notifyForDemande(DemandeAuditoire $demande, int $programmationId, string $auditoireNom): void
    {
        $message = sprintf(
            'Votre cours de %s a été programmé dans l\'auditoire %s le %s de %s à %s.',
            $demande->ec?->nom ?? 'cet EC',
            $auditoireNom,
            $demande->date_debut->format('d/m/Y'),
            substr($demande->heure_debut, 0, 5),
            substr($demande->heure_fin, 0, 5),
        );

        $teacherUsers = $this->teacherUsersForDemande($demande);
        foreach ($teacherUsers as $user) {
            $user->notify(new ProgrammationAttribueeNotification(
                $message,
                $demande->id,
                $programmationId,
                $demande->date_debut->format('Y-m-d'),
                substr($demande->heure_debut, 0, 5),
                substr($demande->heure_fin, 0, 5),
            ));
        }

        $studentUsers = $this->studentUsersForDemande($demande);
        foreach ($studentUsers as $user) {
            $user->notify(new ProgrammationAttribueeNotification(
                sprintf(
                    'Le cours de %s de votre promotion aura lieu dans l\'auditoire %s le %s de %s à %s.',
                    $demande->ec?->nom ?? 'cet EC',
                    $auditoireNom,
                    $demande->date_debut->format('d/m/Y'),
                    substr($demande->heure_debut, 0, 5),
                    substr($demande->heure_fin, 0, 5),
                ),
                $demande->id,
                $programmationId,
                $demande->date_debut->format('Y-m-d'),
                substr($demande->heure_debut, 0, 5),
                substr($demande->heure_fin, 0, 5),
            ));
        }
    }

    protected function teacherUsersForDemande(DemandeAuditoire $demande): Collection
    {
        $teacher = Enseignant::find($demande->enseignant_id);
        if (! $teacher) {
            return collect();
        }

        return User::where('id', $teacher->user_id)
            ->orWhere('email', $teacher->email)
            ->get();
    }

    protected function studentUsersForDemande(DemandeAuditoire $demande): Collection
    {
        $promotionIds = $demande->promotions_concernees ?? [];
        if (empty($promotionIds)) {
            return collect();
        }

        return User::whereHas('role', function ($query) {
            $query->where('nom', 'Étudiant');
        })->whereIn('promotion_id', $promotionIds)->get();
    }
}
