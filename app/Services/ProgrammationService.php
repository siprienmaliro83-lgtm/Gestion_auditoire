<?php

namespace App\Services;

use App\Models\AnneeAcademique;
use App\Models\Auditoire;
use App\Models\DemandeAuditoire;
use App\Models\Programmation;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ProgrammationService
{
    /**
     * Auditoires réellement disponibles pour une demande :
     * état "Disponible", capacité suffisante et aucune réservation
     * (validée) sur la même plage de dates et d'heures.
     */
    public function auditoiresDisponibles(DemandeAuditoire $demande): Collection
    {
        return Auditoire::with('batiment')
            ->where('etat', 'Disponible')
            ->where('capacite', '>=', $demande->effectif_total)
            ->get()
            ->reject(fn (Auditoire $auditoire): bool => $this->hasConflitSalle($auditoire, $demande))
            ->values();
    }

    /**
     * Attribue un auditoire à une demande et crée la programmation.
     * Toutes les règles métier (état, capacité, salle, enseignant)
     * sont vérifiées ici ; en cas de problème, une ValidationException
     * est levée et Laravel redirige vers l'origine avec les erreurs.
     */
    public function attribuer(DemandeAuditoire $demande, Auditoire $auditoire, User $validateur): Programmation
    {
        if (in_array($demande->statut, ['Attribuée', 'Refusée'], true)) {
            throw ValidationException::withMessages([
                'demande' => 'Cette demande a déjà été traitée et ne peut plus être attribuée.',
            ]);
        }

        if ($auditoire->etat !== 'Disponible') {
            throw ValidationException::withMessages([
                'auditoire_id' => 'Cet auditoire n\'est pas disponible ('.($auditoire->etat ?? 'état inconnu').').',
            ]);
        }

        if ($auditoire->capacite < $demande->effectif_total) {
            throw ValidationException::withMessages([
                'auditoire_id' => 'La capacité de l\'auditoire ('.$auditoire->capacite.') est insuffisante pour '.$demande->effectif_total.' étudiants.',
            ]);
        }

        if ($this->hasConflitSalle($auditoire, $demande)) {
            throw ValidationException::withMessages([
                'auditoire_id' => 'Conflit de salle : cet auditoire est déjà réservé sur cette plage horaire.',
            ]);
        }

        if ($this->hasConflitEnseignant($demande)) {
            throw ValidationException::withMessages([
                'enseignant_id' => 'Conflit enseignant : ce professeur est déjà programmé à cette heure.',
            ]);
        }

        $programmation = Programmation::create([
            'demande_auditoire_id' => $demande->id,
            'annee_academique_id' => AnneeAcademique::where('active', true)->value('id'),
            'ec_id' => $demande->ec_id,
            'enseignant_id' => $demande->enseignant_id,
            'auditoire_id' => $auditoire->id,
            'promotions_concernees' => $demande->promotions_concernees,
            'date_debut' => $demande->date_debut,
            'date_fin' => $demande->date_fin,
            'heure_debut' => $demande->heure_debut,
            'heure_fin' => $demande->heure_fin,
            'effectif_total' => $demande->effectif_total,
            'statut' => 'Validée',
            'validee_par' => $validateur->id,
            'validee_a' => now(),
        ]);

        $demande->update(['statut' => 'Attribuée']);

        return $programmation;
    }

    protected function hasConflitSalle(Auditoire $auditoire, DemandeAuditoire $demande): bool
    {
        return Programmation::where('auditoire_id', $auditoire->id)
            ->where('statut', 'Validée')
            ->whereDate('date_debut', '<=', $demande->date_fin)
            ->whereDate('date_fin', '>=', $demande->date_debut)
            ->where(function ($query) use ($demande): void {
                $this->applyTimeOverlap($query, $demande);
            })
            ->exists();
    }

    protected function hasConflitEnseignant(DemandeAuditoire $demande): bool
    {
        return Programmation::where('enseignant_id', $demande->enseignant_id)
            ->where('statut', 'Validée')
            ->whereDate('date_debut', '<=', $demande->date_fin)
            ->whereDate('date_fin', '>=', $demande->date_debut)
            ->where(function ($query) use ($demande): void {
                $this->applyTimeOverlap($query, $demande);
            })
            ->exists();
    }

    protected function applyTimeOverlap($query, DemandeAuditoire $demande): void
    {
        $query->where(function ($overlap) use ($demande): void {
            $overlap->whereBetween('heure_debut', [$demande->heure_debut, $demande->heure_fin])
                ->orWhereBetween('heure_fin', [$demande->heure_debut, $demande->heure_fin])
                ->orWhere(function ($contained) use ($demande): void {
                    $contained->where('heure_debut', '<=', $demande->heure_debut)
                        ->where('heure_fin', '>=', $demande->heure_fin);
                });
        });
    }
}
