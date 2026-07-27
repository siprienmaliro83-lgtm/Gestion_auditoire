<?php

namespace App\Http\Controllers\Decanat;

use App\Http\Controllers\Controller;
use App\Models\Programmation;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ExportController extends Controller
{
    public function pdf(Request $request): Response
    {
        $user = $request->user();
        $programmations = $this->getProgrammations($user);

        $html = $this->buildHtml($programmations, $user);

        $pdf = \Barryvdh\DomPDF\PDF::loadHtml($html)
            ->setPaper('a4', 'landscape')
            ->setOption('isRemoteEnabled', true);

        return $pdf->download('horaires_' . now()->format('Y-m-d') . '.pdf');
    }

    public function excel(Request $request): Response
    {
        $user = $request->user();
        $programmations = $this->getProgrammations($user);

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="horaires_' . now()->format('Y-m-d') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($programmations) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['EC', 'UE', 'Enseignant', 'Auditoire', 'Bâtiment', 'Date', 'Heure début', 'Heure fin', 'Effectif', 'Statut']);

            foreach ($programmations as $p) {
                fputcsv($handle, [
                    $p->ec->nom ?? '',
                    ($p->ec->ue->code ?? '') . ' - ' . ($p->ec->ue->nom ?? ''),
                    ($p->enseignant->prenom ?? '') . ' ' . ($p->enseignant->nom ?? ''),
                    $p->auditoire->nom ?? '',
                    $p->auditoire->batiment->nom ?? '',
                    $p->date_debut->format('d/m/Y'),
                    substr($p->heure_debut, 0, 5),
                    substr($p->heure_fin, 0, 5),
                    $p->effectif_total,
                    $p->statut,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function getProgrammations($user): \Illuminate\Database\Eloquent\Collection
    {
        $promotionIds = Promotion::whereHas('mention.filiere.domaine', function ($q) use ($user) {
            $q->where('id', $user->domaine_id);
        })->pluck('id')->toArray();

        return Programmation::with(['ec.ue', 'enseignant', 'auditoire.batiment'])
            ->where('statut', 'Validée')
            ->where(function ($q) use ($promotionIds) {
                foreach ($promotionIds as $pid) {
                    $q->orWhereJsonContains('promotions_concernees', $pid);
                }
            })
            ->orderBy('date_debut')
            ->orderBy('heure_debut')
            ->get();
    }

    private function buildHtml($programmations, $user): string
    {
        $rows = '';
        foreach ($programmations as $p) {
            $rows .= '<tr>';
            $rows .= '<td>' . e($p->ec->nom ?? '') . '</td>';
            $rows .= '<td>' . e(($p->ec->ue->code ?? '') . ' - ' . ($p->ec->ue->nom ?? '')) . '</td>';
            $rows .= '<td>' . e(($p->enseignant->prenom ?? '') . ' ' . ($p->enseignant->nom ?? '')) . '</td>';
            $rows .= '<td>' . e($p->auditoire->nom ?? '') . '</td>';
            $rows .= '<td>' . e($p->auditoire->batiment->nom ?? '') . '</td>';
            $rows .= '<td>' . e($p->date_debut->format('d/m/Y')) . '</td>';
            $rows .= '<td>' . e(substr($p->heure_debut, 0, 5) . ' - ' . substr($p->heure_fin, 0, 5)) . '</td>';
            $rows .= '<td>' . e($p->effectif_total) . '</td>';
            $rows .= '</tr>';
        }

        return '<!DOCTYPE html><html><head><meta charset="utf-8">
        <style>
            body { font-family: sans-serif; font-size: 11px; }
            h2 { text-align: center; margin-bottom: 5px; }
            .info { text-align: center; color: #666; margin-bottom: 15px; font-size: 12px; }
            table { width: 100%; border-collapse: collapse; }
            th { background: #14213d; color: #fff; padding: 6px 8px; text-align: left; font-size: 10px; }
            td { border: 1px solid #ddd; padding: 5px 8px; font-size: 10px; }
            tr:nth-child(even) { background: #f8f9fa; }
        </style></head><body>
        <h2>Horaires des Programmations</h2>
        <div class="info">Domaine: ' . e($user->domaine?->nom ?? '') . ' | Généré le: ' . now()->format('d/m/Y H:i') . '</div>
        <table>
            <thead><tr><th>EC</th><th>UE</th><th>Enseignant</th><th>Auditoire</th><th>Bâtiment</th><th>Date</th><th>Horaire</th><th>Effectif</th></tr></thead>
            <tbody>' . $rows . '</tbody>
        </table></body></html>';
    }
}
