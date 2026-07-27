<?php

namespace App\Http\Controllers\Etudiant;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class PromotionEtudiantController extends Controller
{
    public function show(): View
    {
        $user = auth()->user()->load('promotion.mention.filiere.domaine');

        return view('etudiant.promotions.show', [
            'user' => $user,
            'promotion' => $user->promotion,
        ]);
    }
}
