<?php
namespace App\Http\Controllers\Admin;

use App\Helpers\LogHelper;
use App\Http\Controllers\Controller;
use App\Models\Ban;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BanController extends Controller
{
    public function index()
    {
        $bans = Ban::with(['user', 'author'])->latest()->paginate(25);
        return view('admin.bans.index', compact('bans'));
    }

    public function store(Request $request, User $user)
    {
        $request->validate(['reason' => 'nullable|string|max:500']);
        Ban::create([
            'user_id'   => $user->id,
            'banned_by' => Auth::id(),
            'reason'    => $request->reason,
        ]);
        $user->update(['is_banned' => true]);
        LogHelper::log('banned_user', 'user', $user->id, ['reason' => $request->reason]);
        return redirect()->back()->with('success', "{$user->name} a été banni.");
    }

    public function destroy(User $user, Ban $ban)
    {
        $ban->delete();
        // Unban if no remaining active bans
        if ($user->bans()->count() === 0) {
            $user->update(['is_banned' => false]);
        }
        LogHelper::log('unbanned_user', 'user', $user->id);
        return redirect()->back()->with('success', "{$user->name} a été débanni.");
    }
}
