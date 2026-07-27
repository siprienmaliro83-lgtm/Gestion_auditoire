<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AttributionAuditoireRequest;
use App\Models\Auditoire;
use App\Models\DemandeAuditoire;
use App\Models\Notification;
use App\Models\Programmation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AttributionAuditoireController extends Controller
{
    public function index(): View
    {
        $demandes = DemandeAuditoire::with(['ec.ue', 'enseignant', 'user'])
            ->whereIn('statut', ['En attente', 'Acceptée'])
            ->latest()
            ->get();

        $auditoires = Auditoire::with('batiment')
            ->where('etat', 'Disponible')
            ->get();

        return view('admin.attributions.index', [
            'demandes' => $demandes,
            'auditoires' => $auditoires,
        ]);
    }

    public function accepter(DemandeAuditoire $demande): RedirectResponse
    {
        $demande->update(['statut' => 'Acceptée']);

        Notification::create([
            'id' => (string) Str::uuid(),
            'type' => 'App\\Notifications\\DemandeStatusChanged',
            'notifiable_type' => \App\Models\User::class,
            'notifiable_id' => $demande->user_id,
            'data' => [
                'message' => sprintf('Votre demande pour "%s" a été acceptée. Vous pouvez maintenant attribuer un auditoire.', $demande->ec?->nom ?? 'EC'),
                'demande_id' => $demande->id,
                'statut' => 'Acceptée',
            ],
        ]);

        return back()->with('success', 'Demande acceptée.');
    }

    public function refuser(Request $request, DemandeAuditoire $demande): RedirectResponse
    {
        $request->validate([
            'motif_refus' => ['required', 'string', 'max:500'],
        ]);

        $demande->update([
            'statut' => 'Refusée',
            'motif_refus' => $request->input('motif_refus'),
        ]);

        Notification::create([
            'id' => (string) Str::uuid(),
            'type' => 'App\\Notifications\\DemandeStatusChanged',
            'notifiable_type' => \App\Models\User::class,
            'notifiable_id' => $demande->user_id,
            'data' => [
                'message' => sprintf('Votre demande pour "%s" a été refusée. Motif: %s', $demande->ec?->nom ?? 'EC', $demande->motif_refus),
                'demande_id' => $demande->id,
                'statut' => 'Refusée',
            ],
        ]);

        return back()->with('success', 'Demande refusée.');
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
            return back()->withErrors(['enseignant_id' => 'Conflit enseignant : ce professeur est déjà programmé dans un autre auditoire à cette heure.']);
        }

        Programmation::create([
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

        $demande->update(['statut' => 'Attribuée']);

        Notification::create([
            'id' => (string) Str::uuid(),
            'type' => 'App\\Notifications\\DemandeStatusChanged',
            'notifiable_type' => \App\Models\User::class,
            'notifiable_id' => $demande->user_id,
            'data' => [
                'message' => sprintf('Un auditoire a été attribué pour "%s". Auditoire: %s.', $demande->ec?->nom ?? 'EC', $auditoire->nom),
                'demande_id' => $demande->id,
                'statut' => 'Attribuée',
            ],
        ]);

        return redirect()->route('admin.attributions.index')->with('success', 'Auditoire attribué avec succès.');
    }
}
