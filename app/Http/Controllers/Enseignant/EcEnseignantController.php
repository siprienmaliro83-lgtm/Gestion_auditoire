<?php

namespace App\Http\Controllers\Enseignant;

use App\Http\Controllers\Controller;
use App\Models\Ec;
use App\Models\Enseignant;
use Illuminate\View\View;

class EcEnseignantController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $enseignant = Enseignant::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();

        $ecs = collect();
        if ($enseignant) {
            $ecs = Ec::with(['ue'])
                ->whereHas('enseignants', function ($q) use ($enseignant) {
                    $q->where('enseignants.id', $enseignant->id);
                })
                ->get();
        }

        return view('enseignant.ecs.index', [
            'ecs' => $ecs,
            'enseignant' => $enseignant,
        ]);
    }
}
