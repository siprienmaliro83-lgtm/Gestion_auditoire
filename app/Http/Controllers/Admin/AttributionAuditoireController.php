<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AttributionAuditoireRequest;
use App\Models\Auditoire;
use App\Models\DemandeAuditoire;
use App\Models\Notification;
use App\Models\Programmation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AttributionAuditoireController extends Controller
{
    public function index(): View
    {
        $demandes = DemandeAuditoire::with(['ec', 'enseignant', 'user'])
            ->where('statut', 'Acceptée')
            ->orWhere('statut', 'En attente')
            ->latest()
            ->get();

        return view('admin.attributions.index', [
            'demandes' => $demandes,
            'auditoires' => Auditoire::with('batiment')->get(),
        ]);
    }

    public function store(AttributionAuditoireRequest $request): RedirectResponse
    {
        $demande = DemandeAuditoire::findOrFail($request->input('demande_auditoire_id'));
        $auditoire = Auditoire::findOrFail($request->input('auditoire_id'));

        if ($auditoire->capacite < $demande->effectif_total) {
            return back()->withErrors(['auditoire_id' => 'La capacité de l\'auditoire est insuffisante.']);
        }

        $conflict = Programmation::where('auditoire_id', $auditoire->id)
            ->where('date_debut', $demande->date_debut)
            ->where('statut', 'Validée')
            ->where(function ($query) use ($demande) {
                $query->whereBetween('heure_debut', [$demande->heure_debut, $demande->heure_fin])
                    ->orWhereBetween('heure_fin', [$demande->heure_debut, $demande->heure_fin]);
            })
            ->exists();

        if ($conflict) {
            return back()->withErrors(['auditoire_id' => 'Conflit de salle : cet auditoire est déjà réservé sur cette plage horaire.']);
        }

        $enseignantConflict = Programmation::where('enseignant_id', $demande->enseignant_id)
            ->where('date_debut', $demande->date_debut)
            ->where('statut', 'Validée')
            ->where(function ($query) use ($demande) {
                $query->whereBetween('heure_debut', [$demande->heure_debut, $demande->heure_fin])
                    ->orWhereBetween('heure_fin', [$demande->heure_debut, $demande->heure_fin]);
            })
            ->exists();

        if ($enseignantConflict) {
            return back()->withErrors(['enseignant_id' => 'Conflit enseignant : ce professeur est déjà programmé à cette heure.']);
        }

        $programming = Programmation::create([
            'demande_auditoire_id' => $demande->id,
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
            'validee_par' => $request->user()->id,
            'validee_a' => now(),
        ]);

        $demande->update([
            'statut' => 'Attribuée',
        ]);

        $message = sprintf(
            'Un auditoire a été attribué pour %s le %s de %s à %s dans %s.',
            $demande->ec?->nom ?? 'Cet EC',
            $demande->date_debut->format('d/m/Y'),
            substr($demande->heure_debut, 0, 5),
            substr($demande->heure_fin, 0, 5),
            $auditoire->nom,
        );

        Notification::create([
            'id' => (string) Str::uuid(),
            'type' => 'App\\Notifications\\ProgrammationAttribuee',
            'notifiable_type' => User::class,
            'notifiable_id' => $demande->enseignant?->user_id ?? $demande->enseignant_id,
            'data' => [
                'message' => $message,
                'programmation_id' => $programming->id,
                'ec_id' => $demande->ec_id,
                'auditoire_id' => $auditoire->id,
                'date_debut' => $demande->date_debut->format('Y-m-d'),
                'heure_debut' => $demande->heure_debut,
                'heure_fin' => $demande->heure_fin,
            ],
        ]);

        $promotionIds = $demande->promotions_concernees ?? [];
        $students = User::where('role_id', function ($query) {
            $query->select('id')->from('roles')->where('nom', 'Étudiant')->limit(1);
        })->whereIn('promotion_id', $promotionIds)->get();

        foreach ($students as $student) {
            Notification::create([
                'id' => (string) Str::uuid(),
                'type' => 'App\\Notifications\\ProgrammationAttribuee',
                'notifiable_type' => User::class,
                'notifiable_id' => $student->id,
                'data' => [
                    'message' => sprintf('Votre promotion a reçu un horaire pour %s le %s de %s à %s dans %s.',
                        $demande->ec?->nom ?? 'cet EC',
                        $demande->date_debut->format('d/m/Y'),
                        substr($demande->heure_debut, 0, 5),
                        substr($demande->heure_fin, 0, 5),
                        $auditoire->nom,
                    ),
                    'programmation_id' => $programming->id,
                    'ec_id' => $demande->ec_id,
                    'auditoire_id' => $auditoire->id,
                    'date_debut' => $demande->date_debut->format('Y-m-d'),
                    'heure_debut' => $demande->heure_debut,
                    'heure_fin' => $demande->heure_fin,
                ],
            ]);
        }

        return redirect()->route('admin.attributions.index')->with('success', 'Auditoire attribué avec succès.');
    }
}
