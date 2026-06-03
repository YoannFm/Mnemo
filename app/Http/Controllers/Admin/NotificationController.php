<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = UserNotification::with('user')->latest()->paginate(25);
        return view('admin.notifications.index', compact('notifications'));
    }

    public function create()
    {
        $users = User::orderBy('name')->get();
        return view('admin.notifications.create', compact('users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'   => 'required|string|max:200',
            'message' => 'nullable|string|max:1000',
            'type'    => 'required|in:info,success,warning,danger',
            'target'  => 'required|in:all,user',
            'user_id' => 'required_if:target,user|nullable|exists:users,id',
        ]);

        if ($data['target'] === 'all') {
            User::chunk(100, function ($users) use ($data) {
                foreach ($users as $user) {
                    UserNotification::create([
                        'user_id' => $user->id,
                        'title'   => $data['title'],
                        'message' => $data['message'] ?? null,
                        'type'    => $data['type'],
                    ]);
                }
            });
            return redirect()->route('admin.notifications.index')
                ->with('success', 'Notification envoyee a tous les utilisateurs.');
        } else {
            UserNotification::create([
                'user_id' => $data['user_id'],
                'title'   => $data['title'],
                'message' => $data['message'] ?? null,
                'type'    => $data['type'],
            ]);
            return redirect()->route('admin.notifications.index')
                ->with('success', 'Notification envoyee.');
        }
    }

    public function destroy(UserNotification $notification)
    {
        $notification->delete();
        return back()->with('success', 'Notification supprimee.');
    }
}
