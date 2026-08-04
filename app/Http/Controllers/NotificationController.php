<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $notifications = Notification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->latest()
            ->paginate(20);

        return view('notifications.index', [
            'notifications' => $notifications,
        ]);
    }

    public function markAsRead(Request $request, Notification $notification): RedirectResponse
    {
        $user = $request->user();

        abort_unless(
            $notification->notifiable_type === get_class($user) && $notification->notifiable_id === $user->id,
            403
        );

        $notification->update(['read_at' => now()]);

        return back()->with('success', 'Notification marquée comme lue.');
    }
}
