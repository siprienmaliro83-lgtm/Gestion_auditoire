<?php

namespace App\Http\Controllers\Decanat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Decanat\DemandeAuditoireRequest;
use App\Models\DemandeAuditoire;
use App\Models\Ec;
use App\Models\Enseignant;
use App\Models\Notification;
use App\Models\Promotion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DemandeAuditoireController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $demandes = DemandeAuditoire::with(['ec.ue', 'enseignant', 'user'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        return view('decanat.demandes.index', [
            'demandes' => $demandes,
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        $domaineId = $user->domaine_id;

        $ecs = Ec::with(['ue', 'enseignants'])
            ->whereHas('ue.programmeAcademique.promotions.mention.filiere.domaine', function ($q) use ($domaineId) {
                $q->where('domaines.id', $domaineId);
            })
            ->where('statut', '!=', 'Entièrement dispensé')
            ->get();

        $enseignantIds = $ecs->pluck('enseignants')->flatten()->pluck('id')->unique();
        $enseignants = Enseignant::with('ecs')->whereIn('id', $enseignantIds)->orderBy('nom')->get();

        $promotions = Promotion::whereHas('mention.filiere.domaine', function ($q) use ($domaineId) {
            $q->where('domaines.id', $domaineId);
        })->get();

        return view('decanat.demandes.form', [
            'ecs' => $ecs,
            'enseignants' => $enseignants,
            'promotions' => $promotions,
            'item' => new DemandeAuditoire(),
        ]);
    }

    public function store(DemandeAuditoireRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        $data['statut'] = 'En attente';
        $data['envoyee_a'] = now();

        DemandeAuditoire::create($data);

        return redirect()->route('decanat.demandes.index')->with('success', 'Demande envoyée à l\'administrateur avec succès.');
    }

    public function show(DemandeAuditoire $demande): View
    {
        return view('decanat.demandes.show', [
            'demande' => $demande->load(['ec.ue', 'enseignant', 'user']),
        ]);
    }

    public function updateStatus(Request $request, DemandeAuditoire $demande): RedirectResponse
    {
        $request->validate([
            'statut' => ['required', 'in:En attente,Acceptée,Refusée,Attribuée'],
            'motif_refus' => ['nullable', 'string'],
        ]);

        $demande->update($request->only(['statut', 'motif_refus']));

        Notification::create([
            'id' => (string) Str::uuid(),
            'type' => 'App\\Notifications\\DemandeStatusChanged',
            'notifiable_type' => \App\Models\User::class,
            'notifiable_id' => $demande->user_id,
            'data' => [
                'message' => sprintf('La demande pour "%s" a été mise à jour : %s.', $demande->ec?->nom ?? 'EC', $demande->statut),
                'demande_id' => $demande->id,
                'statut' => $demande->statut,
            ],
        ]);

        return back()->with('success', 'Statut de la demande mis à jour.');
    }
}
