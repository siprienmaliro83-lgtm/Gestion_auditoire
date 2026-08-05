<?php

namespace App\Http\Controllers;

use App\Models\Filiere;
use App\Models\Mention;
use App\Models\Promotion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AcademiqueApiController extends Controller
{
    public function filieres(Request $request): JsonResponse
    {
        $data = $request->validate([
            'domaine_id' => ['required', 'exists:domaines,id'],
        ]);

        $filieres = Filiere::where('domaine_id', $data['domaine_id'])
            ->orderBy('nom')
            ->get()
            ->map(fn (Filiere $filiere): array => [
                'id' => $filiere->id,
                'code' => $filiere->code,
                'nom' => $filiere->nom,
            ]);

        return response()->json($filieres);
    }

    public function mentions(Request $request): JsonResponse
    {
        $data = $request->validate([
            'filiere_id' => ['required', 'exists:filieres,id'],
        ]);

        $mentions = Mention::where('filiere_id', $data['filiere_id'])
            ->orderBy('nom')
            ->get()
            ->map(fn (Mention $mention): array => [
                'id' => $mention->id,
                'code' => $mention->code,
                'nom' => $mention->nom,
            ]);

        return response()->json($mentions);
    }

    public function promotions(Request $request): JsonResponse
    {
        $data = $request->validate([
            'mention_id' => ['required', 'exists:mentions,id'],
        ]);

        $promotions = Promotion::where('mention_id', $data['mention_id'])
            ->orderBy('nom')
            ->get()
            ->map(fn (Promotion $promotion): array => [
                'id' => $promotion->id,
                'code' => $promotion->code,
                'nom' => $promotion->nom,
                'niveau' => $promotion->niveau,
            ]);

        return response()->json($promotions);
    }
}
