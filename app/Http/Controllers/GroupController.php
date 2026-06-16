<?php

namespace App\Http\Controllers;

use App\Helpers\LogHelper;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    public function index()
    {
        $groups = Group::where('owner_id', Auth::id())
            ->withCount('members')
            ->latest()
            ->get();

        $memberOf = Auth::user()->groups()->withCount('members')->with('owner')->get();

        return view('groups.index', compact('groups', 'memberOf'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $group = Group::create([
            'owner_id'    => Auth::id(),
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        LogHelper::log('created_group', 'group', $group->id, ['name' => $group->name]);

        return redirect()->route('groups.index')->with('success', 'Groupe créé.');
    }

    public function show(Group $group)
    {
        if ($group->owner_id !== Auth::id()) {
            abort(403);
        }

        $group->load('members');
        $memberIds = $group->members->pluck('id')->toArray();

        return view('groups.show', compact('group', 'memberIds'));
    }

    public function destroy(Group $group)
    {
        if ($group->owner_id !== Auth::id()) {
            abort(403);
        }

        LogHelper::log('deleted_group', 'group', $group->id, ['name' => $group->name], 'warning');

        $group->delete();

        return redirect()->route('groups.index')->with('success', 'Groupe supprimé.');
    }

    public function addMember(Request $request, Group $group)
    {
        if ($group->owner_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate(['email' => 'required|email|exists:users,email']);
        $user = User::where('email', $validated['email'])->first();

        if ($group->members()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'Cet utilisateur est déjà dans le groupe.');
        }

        $group->members()->attach($user->id, ['joined_at' => now()]);

        LogHelper::log('added_group_member', 'group', $group->id, ['user_id' => $user->id]);

        return back()->with('success', $user->name . ' ajouté au groupe.');
    }

    public function removeMember(Group $group, User $user)
    {
        if ($group->owner_id !== Auth::id()) {
            abort(403);
        }

        $group->members()->detach($user->id);

        LogHelper::log('removed_group_member', 'group', $group->id, ['user_id' => $user->id], 'warning');

        return back()->with('success', 'Membre retiré du groupe.');
    }
}
