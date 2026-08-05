<?php

namespace App\Http\Controllers\Enseignant;

use App\Http\Controllers\Controller;
use App\Models\Programmation;
use Illuminate\View\View;

class ProgrammationEnseignantController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $enseignant = $user->enseignant;

        $programmations = collect();
        if ($enseignant) {
            $programmations = Programmation::with(['ec.ue', 'enseignant', 'auditoire.batiment'])
                ->where('enseignant_id', $enseignant->id)
                ->where('statut', 'Validée')
                ->orderBy('date_debut')
                ->orderBy('heure_debut')
                ->paginate(20);
        }

        return view('enseignant.programmations.index', [
            'programmations' => $programmations,
            'enseignant' => $enseignant,
        ]);
    }
}
