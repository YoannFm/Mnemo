<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\LogHelper;
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

    public function unmute(\Illuminate\Http\Request $request, AdminSanction $sanction)
    {
        if (!$sanction->user) {
            return back()->with('error', 'Utilisateur introuvable.');
        }

        Mute::where('user_id', $sanction->user_id)
            ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->delete();

        $reason = trim($request->input('reason', ''));
        $message = 'Votre restriction de commentaires a été levée par un administrateur.';
        if ($reason !== '') {
            $message .= ' Raison : ' . $reason;
        } else {
            $message .= ' Aucune raison spécifiée.';
        }

        UserNotification::create([
            'user_id' => $sanction->user_id,
            'title'   => 'Votre mute a été levé',
            'message' => $message,
            'type'    => 'info',
        ]);

        LogHelper::log('deleted_sanction', 'user', $sanction->user_id, ['user_id' => $sanction->user_id, 'type' => 'mute', 'reason' => $reason ?: null], 'warning');
        return back()->with('success', 'Utilisateur démute avec succès.');
    }
}
