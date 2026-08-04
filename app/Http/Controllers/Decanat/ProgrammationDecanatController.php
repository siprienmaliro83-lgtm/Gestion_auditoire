<?php

namespace App\Http\Controllers\Decanat;

use App\Http\Controllers\Controller;
use App\Models\Programmation;
use App\Models\Promotion;
use App\Models\Ec;
use App\Models\Enseignant;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProgrammationDecanatController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();

        $promotionIds = Promotion::whereHas('mention.filiere.domaine', function ($q) use ($user) {
            $q->where('id', $user->domaine_id);
        })->pluck('id')->toArray();

        $promotions = Promotion::whereHas('mention.filiere.domaine', function ($q) use ($user) {
            $q->where('id', $user->domaine_id);
        })->orderBy('nom')->get();

        $selectedPromotion = $request->input('promotion_id');
        $selectedDate = $request->input('date', now()->format('Y-m-d'));

        $query = Programmation::with(['ec.ue', 'enseignant', 'auditoire.batiment'])
            ->where('statut', 'Validée')
            ->where(function ($q) use ($promotionIds, $selectedPromotion) {
                if ($selectedPromotion) {
                    $q->whereJsonContains('promotions_concernees', (int) $selectedPromotion);
                } else {
                    foreach ($promotionIds as $pid) {
                        $q->orWhereJsonContains('promotions_concernees', $pid);
                    }
                }
            });

        if ($selectedDate) {
            $query->where('date_debut', $selectedDate);
        }

        $programmations = $query->orderBy('heure_debut')->get();

        $weekStart = \Carbon\Carbon::parse($selectedDate)->startOfWeek();
        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $weekDays[] = $weekStart->copy()->addDays($i);
        }

        $timeSlots = [];
        for ($h = 7; $h <= 20; $h++) {
            $timeSlots[] = sprintf('%02d:00', $h);
        }

        $grid = [];
        foreach ($timeSlots as $slot) {
            foreach ($weekDays as $day) {
                $grid[$slot][$day->format('Y-m-d')] = [];
            }
        }

        foreach ($programmations as $prog) {
            $dateKey = $prog->date_debut->format('Y-m-d');
            $hourKey = substr($prog->heure_debut, 0, 5);
            $endHour = intval(substr($prog->heure_fin, 0, 2));
            $startHour = intval(substr($prog->heure_debut, 0, 2));

            for ($h = $startHour; $h < $endHour; $h++) {
                $slotKey = sprintf('%02d:00', $h);
                if (isset($grid[$slotKey][$dateKey])) {
                    $grid[$slotKey][$dateKey][] = $prog;
                }
            }
        }

        return view('decanat.programmations.index', [
            'programmations' => $programmations,
            'promotions' => $promotions,
            'promotionIds' => $promotionIds,
            'selectedPromotion' => $selectedPromotion,
            'selectedDate' => $selectedDate,
            'weekDays' => $weekDays,
            'timeSlots' => $timeSlots,
            'grid' => $grid,
        ]);
    }
}
