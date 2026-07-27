<?php

namespace App\Http\Controllers\Etudiant;

use App\Http\Controllers\Controller;
use App\Models\Programmation;
use Illuminate\View\View;

class ProgrammationEtudiantController extends Controller
{
    public function index(): View
    {
        $user = auth()->user()->load('promotion.mention.filiere.domaine');

        $promotionId = $user->promotion_id;

        $programmations = Programmation::with(['ec.ue', 'enseignant', 'auditoire.batiment'])
            ->where('statut', 'Validée')
            ->where(function ($q) use ($promotionId) {
                $q->whereJsonContains('promotions_concernees', $promotionId);
            })
            ->orderBy('date_debut')
            ->orderBy('heure_debut')
            ->paginate(20);

        return view('etudiant.programmations.index', [
            'programmations' => $programmations,
            'promotion' => $user->promotion,
        ]);
    }
}
