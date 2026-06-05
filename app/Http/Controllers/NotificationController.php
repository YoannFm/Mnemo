<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->userNotifications()->paginate(20);
        auth()->user()->userNotifications()->whereNull('read_at')->update(['read_at' => now()]);
        return view('notifications.index', compact('notifications'));
    }

    public function markRead(UserNotification $notification)
    {
        if ($notification->user_id === auth()->id() || auth()->user()?->is_admin) {
            $notification->update(['read_at' => now()]);
        }
        return back();
    }

    public function markAllRead()
    {
        auth()->user()->userNotifications()->whereNull('read_at')->update(['read_at' => now()]);
        return back()->with('success', 'Toutes les notifications marquees comme lues.');
    }
}
