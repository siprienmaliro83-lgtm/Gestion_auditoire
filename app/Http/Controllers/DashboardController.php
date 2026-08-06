<?php

namespace App\Http\Controllers;

use App\Models\Auditoire;
use App\Models\Batiment;
use App\Models\DemandeAuditoire;
use App\Models\Ec;
use App\Models\Enseignant;
use App\Models\Notification;
use App\Models\Programmation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user()->load('role', 'domaine', 'promotion.mention.filiere.domaine');
        $role = $user->role?->nom;

        if ($role === 'Administrateur') {
            return $this->admin($user, $role);
        }

        if ($role === 'Décanat') {
            return $this->decanat($user, $role);
        }

        if ($role === 'Enseignant') {
            return $this->enseignant($user, $role);
        }

        if ($role === 'Étudiant') {
            return $this->etudiant($user, $role);
        }

        return view('dashboard.index', compact('user', 'role'));
    }

    private function admin($user, $role): View
    {
        $stats = [
            'domaines' => \App\Models\Domaine::count(),
            'decanats' => \App\Models\User::whereHas('role', fn ($q) => $q->whereIn('nom', ['Décanat', 'Administrateur']))->where('confirme', true)->count(),
            'auditoires' => Auditoire::count(),
            'batiments' => Batiment::count(),
            'enseignants' => Enseignant::count(),
            'ecs' => Ec::count(),
            'programmations' => Programmation::where('statut', 'Validée')->count(),
            'demandes_en_attente' => DemandeAuditoire::where('statut', 'En attente')->count(),
            'demandes_validees' => DemandeAuditoire::whereIn('statut', ['Acceptée', 'Attribuée'])->count(),
            'utilisateurs_non_confirme' => \App\Models\User::where('confirme', false)->count(),
        ];

        return view('dashboard.index', [
            'user' => $user,
            'role' => $role,
            'stats' => $stats,
            'recentDemandes' => DemandeAuditoire::with(['ec', 'enseignant', 'user'])
                ->latest()
                ->take(5)
                ->get(),
            'recentProgrammations' => Programmation::with(['ec', 'enseignant', 'auditoire.batiment'])
                ->latest()
                ->take(5)
                ->get(),
            'recentNotifications' => $user->notifications()->latest()->take(5)->get(),
        ]);
    }

    private function decanat($user, $role): View
    {
        $stats = [
            'mes_demandes' => DemandeAuditoire::where('user_id', $user->id)->count(),
            'en_attente' => DemandeAuditoire::where('user_id', $user->id)->where('statut', 'En attente')->count(),
            'acceptees' => DemandeAuditoire::where('user_id', $user->id)->where('statut', 'Acceptée')->count(),
            'attribuees' => DemandeAuditoire::where('user_id', $user->id)->where('statut', 'Attribuée')->count(),
            'refusees' => DemandeAuditoire::where('user_id', $user->id)->where('statut', 'Refusée')->count(),
        ];

        return view('dashboard.index', [
            'user' => $user,
            'role' => $role,
            'stats' => $stats,
            'recentDemandes' => DemandeAuditoire::with(['ec', 'enseignant'])
                ->where('user_id', $user->id)
                ->latest()
                ->take(5)
                ->get(),
            'recentNotifications' => $user->notifications()->latest()->take(5)->get(),
        ]);
    }

    private function enseignant($user, $role): View
    {
        $enseignant = $user->enseignant;
        $enseignantId = $enseignant?->id;

        $stats = [
            'mes_ecs' => $enseignant ? $enseignant->ecs()->count() : 0,
            'mes_programmations' => $enseignantId ? Programmation::where('enseignant_id', $enseignantId)->where('statut', 'Validée')->count() : 0,
        ];

        $recentProgrammations = $enseignantId
            ? Programmation::with(['ec.ue', 'auditoire.batiment'])
                ->where('enseignant_id', $enseignantId)
                ->where('statut', 'Validée')
                ->latest()
                ->take(5)
                ->get()
            : collect();

        return view('dashboard.index', [
            'user' => $user,
            'role' => $role,
            'stats' => $stats,
            'recentProgrammations' => $recentProgrammations,
            'recentNotifications' => $user->notifications()->latest()->take(5)->get(),
        ]);
    }

    private function etudiant($user, $role): View
    {
        $promotionId = $user->promotion_id;

        $stats = [
            'mes_programmations' => $promotionId
                ? Programmation::where('statut', 'Validée')
                    ->whereJsonContains('promotions_concernees', $promotionId)
                    ->count()
                : 0,
        ];

        $recentProgrammations = $promotionId
            ? Programmation::with(['ec.ue', 'enseignant', 'auditoire.batiment'])
                ->where('statut', 'Validée')
                ->whereJsonContains('promotions_concernees', $promotionId)
                ->latest()
                ->take(5)
                ->get()
            : collect();

        return view('dashboard.index', [
            'user' => $user,
            'role' => $role,
            'stats' => $stats,
            'recentProgrammations' => $recentProgrammations,
            'recentNotifications' => $user->notifications()->latest()->take(5)->get(),
        ]);
    }
}
