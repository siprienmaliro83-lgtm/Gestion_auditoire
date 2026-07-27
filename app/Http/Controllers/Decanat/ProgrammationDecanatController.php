<?php

namespace App\Http\Controllers\Decanat;

use App\Http\Controllers\Controller;
use App\Models\Programmation;
use App\Models\Promotion;
use Illuminate\View\View;

class ProgrammationDecanatController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $promotionIds = Promotion::whereHas('mention.filiere.domaine', function ($q) use ($user) {
            $q->where('id', $user->domaine_id);
        })->pluck('id')->toArray();

        $programmations = Programmation::with(['ec.ue', 'enseignant', 'auditoire.batiment'])
            ->where(function ($q) use ($promotionIds) {
                foreach ($promotionIds as $pid) {
                    $q->orWhereJsonContains('promotions_concernees', $pid);
                }
            })
            ->orderBy('date_debut')
            ->orderBy('heure_debut')
            ->paginate(20);

        return view('decanat.programmations.index', [
            'programmations' => $programmations,
        ]);
    }
}
