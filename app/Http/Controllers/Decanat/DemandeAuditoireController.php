<?php

namespace App\Http\Controllers\Decanat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Decanat\DemandeAuditoireRequest;
use App\Models\DemandeAuditoire;
use App\Models\Ec;
use App\Models\Enseignant;
use App\Models\Promotion;
use App\Services\ProgrammationNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DemandeAuditoireController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $demandes = DemandeAuditoire::with(['ec', 'enseignant', 'user'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        return view('decanat.demandes.index', [
            'demandes' => $demandes,
        ]);
    }

    public function create(): View
    {
        $user = auth()->user();
        $promotions = Promotion::with('mention')
            ->whereHas('mention.filiere.domaine', function ($query) use ($user) {
                $query->where('id', $user->domaine_id);
            })
            ->orderBy('nom')
            ->get();

        return view('decanat.demandes.form', [
            'ecs' => Ec::with('ue')
                ->whereHas('ue.programmeAcademique.promotions.mention.filiere.domaine', function ($query) use ($user) {
                    $query->where('id', $user->domaine_id);
                })
                ->orderBy('code')
                ->get(),
            'enseignants' => Enseignant::where('statut', 'Actif')->orderBy('nom')->orderBy('prenom')->get(),
            'promotions' => $promotions,
            'item' => new DemandeAuditoire(),
        ]);
    }

    public function store(DemandeAuditoireRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $allowedPromotionIds = Promotion::whereHas('mention.filiere.domaine', function ($query) use ($user) {
            $query->where('id', $user->domaine_id);
        })->pluck('id')->toArray();

        $invalidPromotions = array_diff($data['promotions_concernees'], $allowedPromotionIds);
        if (! empty($invalidPromotions)) {
            return back()
                ->withErrors(['promotions_concernees' => 'Certaines promotions sélectionnées ne sont pas dans votre domaine.'])
                ->withInput();
        }

        $data['user_id'] = $user->id;
        $data['statut'] = 'En attente';
        $data['envoyee_a'] = now();

        DemandeAuditoire::create($data);

        return redirect()->route('decanat.demandes.index')->with('success', 'Demande créée avec succès.');
    }

    public function show(DemandeAuditoire $demande): View
    {
        abort_unless(auth()->user()->id === $demande->user_id, 403);

        return view('decanat.demandes.show', [
            'demande' => $demande->load(['ec', 'enseignant', 'user']),
        ]);
    }

    public function updateStatus(Request $request, DemandeAuditoire $demande, ProgrammationNotificationService $notificationService): RedirectResponse
    {
        $request->validate([
            'statut' => ['required', 'in:En attente,Acceptée,Refusée,Attribuée'],
            'motif_refus' => ['nullable', 'string'],
        ]);

        $demande->update($request->only(['statut', 'motif_refus']));

        $notificationService->notifyStatut($demande, $demande->statut);

        return back()->with('success', 'Statut de la demande mis à jour.');
    }
}
