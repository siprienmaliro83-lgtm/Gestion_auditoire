<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AttributionAuditoireRequest;
use App\Http\Requests\Admin\RejetDemandeRequest;
use App\Models\Auditoire;
use App\Models\DemandeAuditoire;
use App\Services\ProgrammationNotificationService;
use App\Services\ProgrammationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttributionAuditoireController extends Controller
{
    public function index(Request $request, ProgrammationService $service): View
    {
        $demandes = DemandeAuditoire::with(['ec', 'enseignant', 'user'])
            ->whereIn('statut', ['En attente', 'Acceptée', 'Attribuée'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $auditoiresDisponibles = $demandes
            ->getCollection()
            ->mapWithKeys(fn (DemandeAuditoire $demande) => [
                $demande->id => $service->auditoiresDisponibles($demande),
            ]);

        return view('admin.attributions.index', [
            'demandes' => $demandes,
            'auditoires' => Auditoire::with('batiment')->get(),
            'auditoiresDisponibles' => $auditoiresDisponibles,
        ]);
    }

    public function show(DemandeAuditoire $demande, ProgrammationService $service): View
    {
        abort_unless(
            in_array($demande->statut, ['En attente', 'Acceptée', 'Attribuée', 'Refusée'], true),
            404
        );

        return view('admin.attributions.show', [
            'demande' => $demande->load(['ec.ue', 'enseignant', 'user', 'programmation.auditoire']),
            'auditoiresDisponibles' => $service->auditoiresDisponibles($demande),
        ]);
    }

    public function store(
        AttributionAuditoireRequest $request,
        ProgrammationService $service,
        ProgrammationNotificationService $notificationService,
    ): RedirectResponse {
        $demande = DemandeAuditoire::findOrFail($request->validated('demande_auditoire_id'));
        $auditoire = Auditoire::findOrFail($request->validated('auditoire_id'));

        $programmation = $service->attribuer($demande, $auditoire, $request->user());

        $notificationService->notifyForDemande($demande, $programmation->id, $auditoire->nom);

        return redirect()
            ->route('admin.attributions.show', $demande)
            ->with('success', 'Auditoire attribué avec succès. La programmation a été créée.');
    }

    public function rejeter(RejetDemandeRequest $request, DemandeAuditoire $demande, ProgrammationNotificationService $notificationService): RedirectResponse
    {
        if (in_array($demande->statut, ['Attribuée', 'Refusée'], true)) {
            return back()->withErrors(['demande' => 'Cette demande a déjà été traitée.']);
        }

        $demande->update([
            'statut' => 'Refusée',
            'motif_refus' => $request->validated('motif_refus'),
        ]);

        $notificationService->notifyStatut($demande, 'Refusée');

        return redirect()
            ->route('admin.attributions.index')
            ->with('success', 'Demande refusée et motif enregistré.');
    }
}
