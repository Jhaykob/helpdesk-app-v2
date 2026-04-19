<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Fetch all notifications (both read and unread), paginated
        $notifications = $user->notifications()->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Find the specific notification regardless of read status
        $notification = $user->notifications()->find($id);

        if ($notification) {
            // Force the database update
            $notification->markAsRead();

            // Safely redirect to the relative URL, fallback to dashboard if missing
            return redirect($notification->data['url'] ?? route('dashboard'));
        }

        return back()->withErrors(['error' => 'Notification not found.']);
    }

    public function markAllAsRead()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $user->unreadNotifications->markAsRead();
        return back();
    }
}
