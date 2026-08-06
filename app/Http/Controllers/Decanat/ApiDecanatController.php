<?php

namespace App\Http\Controllers\Decanat;

use App\Http\Controllers\Controller;
use App\Models\Ec;
use App\Models\Enseignant;
use App\Models\Promotion;
use App\Models\Programmation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiDecanatController extends Controller
{
    public function ecs(Request $request): JsonResponse
    {
        $user = $request->user();
        $domaineId = $user->domaine_id;

        $ecs = Ec::with(['ue'])
            ->whereHas('ue.programmeAcademique.promotions.mention.filiere.domaine', function ($q) use ($domaineId) {
                $q->where('domaines.id', $domaineId);
            })
            ->where('statut', '!=', 'Entièrement dispensé')
            ->orderBy('code')
            ->get()
            ->map(fn ($ec) => [
                'id' => $ec->id,
                'code' => $ec->code,
                'nom' => $ec->nom,
                'volume_horaire' => $ec->volume_horaire,
                'ue_nom' => $ec->ue?->nom ?? 'Sans UE',
                'ue_id' => $ec->ue_id,
                'statut' => $ec->statut,
                'enseignant_ids' => $ec->enseignants->pluck('id')->toArray(),
            ]);

        return response()->json($ecs);
    }

    public function enseignants(Request $request): JsonResponse
    {
        $enseignants = Enseignant::with('ecs')
            ->where('statut', 'Actif')
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get();

        return response()->json($enseignants->map(fn ($ens) => [
            'id' => $ens->id,
            'nom' => $ens->nom,
            'prenom' => $ens->prenom,
            'email' => $ens->email,
            'grade' => $ens->grade,
            'ec_ids' => $ens->ecs->pluck('id')->toArray(),
        ]));
    }

    public function promotions(Request $request): JsonResponse
    {
        $user = $request->user();
        $domaineId = $user->domaine_id;

        $promotions = Promotion::with('mention')
            ->whereHas('mention.filiere.domaine', function ($q) use ($domaineId) {
                $q->where('domaines.id', $domaineId);
            })
            ->orderBy('nom')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'nom' => $p->nom,
                'code' => $p->code,
                'niveau' => $p->niveau,
                'effectif' => $p->effectif,
                'mention_nom' => $p->mention?->nom ?? '',
            ]);

        return response()->json($promotions);
    }

    public function disponible(Request $request): JsonResponse
    {
        $request->validate([
            'ec_id' => ['required', 'exists:ecs,id'],
            'date' => ['required', 'date'],
            'heure_debut' => ['required', 'date_format:H:i'],
            'heure_fin' => ['required', 'date_format:H:i', 'after:heure_debut'],
        ]);

        $ec = Ec::find($request->ec_id);
        $enseignantIds = $ec->enseignants->pluck('id')->toArray();

        $occupiedEnseignants = Programmation::where('date_debut', $request->date)
            ->where('statut', 'Validée')
            ->where(function ($q) use ($request) {
                $q->whereBetween('heure_debut', [$request->heure_debut, $request->heure_fin])
                    ->orWhereBetween('heure_fin', [$request->heure_debut, $request->heure_fin]);
            })
            ->whereIn('enseignant_id', $enseignantIds)
            ->pluck('enseignant_id')
            ->toArray();

        $availableEnseignants = Enseignant::whereIn('id', $enseignantIds)
            ->whereNotIn('id', $occupiedEnseignants)
            ->get()
            ->map(fn ($ens) => [
                'id' => $ens->id,
                'nom' => $ens->nom,
                'prenom' => $ens->prenom,
                'grade' => $ens->grade,
            ]);

        return response()->json($availableEnseignants);
    }
}
