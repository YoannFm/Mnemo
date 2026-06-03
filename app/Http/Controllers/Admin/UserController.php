<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($roleId = $request->input('role_id')) {
            $query->where('role_id', $roleId);
        }

        $roles = Role::orderBy('power', 'desc')->get();
        $users = $query->with('role')->orderBy('id')->paginate(20)->withQueryString();
        $allUsers = User::orderBy('name')->get(['id', 'name', 'email']);

        return view('admin.users.index', compact('users', 'roles', 'allUsers'));
    }

    public function create()
    {
        $roles = Role::orderBy('power', 'desc')->get();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
            'role_id'  => 'nullable|exists:roles,id',
        ]);

        $role = $data['role_id'] ? Role::find($data['role_id']) : null;

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id'  => $data['role_id'] ?? null,
            'is_admin' => $role && $role->is_admin_role,
        ]);

        return redirect()->route('admin.users.edit', $user)
            ->with('success', 'Utilisateur ' . $user->name . ' cree.');
    }

    public function edit(User $user)
    {
        $roles = Role::orderBy('power', 'desc')->get();
        $allUsers = User::orderBy('name')->get(['id', 'name', 'email']);
        $userLogs = ActivityLog::where('user_id', $user->id)->latest()->paginate(15)->appends(request()->query());
        return view('admin.users.edit', compact('user', 'roles', 'allUsers', 'userLogs'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'role_id'  => 'nullable|exists:roles,id',
        ]);

        $user->name  = $data['name'];
        $user->email = $data['email'];

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        if (!empty($data['role_id'])) {
            $user->role_id = $data['role_id'];
            $role = Role::find($data['role_id']);
            $user->is_admin = $role && $role->is_admin_role;
        }

        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur ' . $user->name . ' mis a jour.');
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $user->delete();

        return back()->with('success', 'Utilisateur supprime.');
    }

    public function forcePasswordChange(User $user)
    {
        $user->force_password_change = true;
        $user->save();

        return back()->with('success', 'L\'utilisateur devra changer son mot de passe a la prochaine connexion.');
    }
}
