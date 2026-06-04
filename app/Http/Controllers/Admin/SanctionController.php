<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSanction;
use App\Models\Mute;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Auth;

class SanctionController extends Controller
{
    public function index()
    {
        $sanctions = AdminSanction::with(['user', 'admin'])
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return view('admin.sanctions.index', compact('sanctions'));
    }

    public function unmute(AdminSanction $sanction)
    {
        if (!$sanction->user) {
            return back()->with('error', 'Utilisateur introuvable.');
        }

        Mute::where('user_id', $sanction->user_id)
            ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->delete();

        UserNotification::create([
            'user_id' => $sanction->user_id,
            'title'   => 'Votre mute a été levé',
            'message' => 'Votre restriction de commentaires a été levée par un administrateur.',
            'type'    => 'info',
        ]);

        return back()->with('success', 'Utilisateur démute avec succès.');
    }
}
