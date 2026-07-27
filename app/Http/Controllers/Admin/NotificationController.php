<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Programmation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = Notification::latest()->paginate(10);

        return view('admin.notifications.index', [
            'notifications' => $notifications,
        ]);
    }

    public function markAsRead(Notification $notification): RedirectResponse
    {
        $notification->update(['read_at' => now()]);

        return back()->with('success', 'Notification marquée comme lue.');
    }

    public function exportProgramations(): Response
    {
        $rows = Programmation::with(['ec', 'enseignant', 'auditoire.batiment'])->latest()->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="programmations.csv"',
        ];

        $content = fopen('php://memory', 'w+');
        fputcsv($content, ['EC', 'Enseignant', 'Auditoire', 'Bâtiment', 'Date', 'Heure début', 'Heure fin', 'Statut']);

        foreach ($rows as $row) {
            fputcsv($content, [
                $row->ec?->nom,
                $row->enseignant?->nom,
                $row->auditoire?->nom,
                $row->auditoire?->batiment?->nom,
                $row->date_debut?->format('d/m/Y'),
                $row->heure_debut,
                $row->heure_fin,
                $row->statut,
            ]);
        }

        rewind($content);
        $csv = stream_get_contents($content);
        fclose($content);

        return response($csv, 200, $headers);
    }
}
