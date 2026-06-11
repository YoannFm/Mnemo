<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\LogHelper;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\HttpFoundation\JsonResponse;

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

        LogHelper::log('admin_created_user', 'user', $user->id, ['email' => $user->email]);

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

        LogHelper::log('admin_updated_user', 'user', $user->id, ['name' => $user->name]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur ' . $user->name . ' mis a jour.');
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $userId = $user->id;
        $email  = $user->email;

        $user->delete();

        LogHelper::log('admin_deleted_user', 'user', $userId, ['email' => $email], 'warning');

        return back()->with('success', 'Utilisateur supprime.');
    }

    public function forcePasswordChange(User $user)
    {
        $user->force_password_change = true;
        $user->save();

        LogHelper::log('admin_forced_password_change', 'user', $user->id);

        return back()->with('success', 'L\'utilisateur devra changer son mot de passe a la prochaine connexion.');
    }

    public function exportData(User $user)
    {
        $user->load([
            'role',
            'modules.items',
            'progresses',
            'scores',
            'bans',
            'userNotifications',
        ]);

        $activityLogs = ActivityLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($l) => [
                'date'        => $l->created_at?->format('d/m/Y H:i'),
                'action'      => $l->getActionMessage(),
                'description' => $l->data,
                'level'       => $l->level,
            ])->values();

        $data = [
            'account' => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'created_at' => $user->created_at?->toIso8601String(),
                'role'       => $user->role ? [
                    'id'   => $user->role->id,
                    'name' => $user->role->name,
                ] : null,
                'is_admin'   => $user->is_admin,
                'is_banned'  => $user->is_banned,
            ],
            'modules' => $user->modules->map(fn ($m) => [
                'id'          => $m->id,
                'title'       => $m->title,
                'description' => $m->description,
                'is_public'   => $m->is_public,
                'created_at'  => $m->created_at?->toIso8601String(),
                'items'       => $m->items->map(fn ($i) => [
                    'id'         => $i->id,
                    'question'   => $i->question ?? null,
                    'answer'     => $i->answer ?? null,
                    'created_at' => $i->created_at?->toIso8601String(),
                ])->values(),
            ])->values(),
            'progress'       => $user->progresses->map(fn ($p) => $p->toArray())->values(),
            'scores'         => $user->scores->map(fn ($s) => $s->toArray())->values(),
            'bans'           => $user->bans->map(fn ($b) => [
                'id'         => $b->id,
                'reason'     => $b->reason,
                'created_at' => $b->created_at?->toIso8601String(),
            ])->values(),
            'notifications'  => $user->userNotifications->map(fn ($n) => $n->toArray())->values(),
            'activity_logs'  => $activityLogs,
            'exported_at'    => now()->toIso8601String(),
        ];

        $filename = "user_{$user->id}_data.json";
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return response($json, 200, [
            'Content-Type'        => 'application/json',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function exportAll()
    {
        $users = User::with([
            'role',
            'modules.items',
            'progresses',
            'scores',
            'bans',
            'userNotifications',
        ])->get();

        $zip = new \ZipArchive();
        $zipPath = storage_path('app/tmp_export_users_' . time() . '.zip');
        $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $allLogs = \App\Models\ActivityLog::orderBy('created_at', 'desc')
            ->get()
            ->groupBy('user_id');

        foreach ($users as $user) {
            $userLogs = ($allLogs[$user->id] ?? collect())->map(fn ($l) => [
                'date'        => $l->created_at?->format('d/m/Y H:i'),
                'action'      => $l->getActionMessage(),
                'description' => $l->data,
                'level'       => $l->level,
            ])->values();

            $data = [
                'account' => [
                    'id'         => $user->id,
                    'name'       => $user->name,
                    'email'      => $user->email,
                    'created_at' => $user->created_at?->toIso8601String(),
                    'role'       => $user->role ? ['id' => $user->role->id, 'name' => $user->role->name] : null,
                    'is_admin'   => $user->is_admin,
                    'is_banned'  => $user->is_banned,
                    'two_factor_enabled' => !empty($user->two_factor_secret),
                    'last_login_at' => $user->last_login_at?->toIso8601String(),
                ],
                'modules' => $user->modules->map(fn ($m) => [
                    'id'          => $m->id,
                    'title'       => $m->title,
                    'description' => $m->description,
                    'is_public'   => $m->is_public,
                    'created_at'  => $m->created_at?->toIso8601String(),
                    'items'       => $m->items->map(fn ($i) => [
                        'id'         => $i->id,
                        'question'   => $i->question ?? null,
                        'answer'     => $i->answer ?? null,
                        'created_at' => $i->created_at?->toIso8601String(),
                    ])->values(),
                ])->values(),
                'progress'      => $user->progresses->map(fn ($p) => $p->toArray())->values(),
                'scores'        => $user->scores->map(fn ($s) => $s->toArray())->values(),
                'bans'          => $user->bans->map(fn ($b) => [
                    'id'         => $b->id,
                    'reason'     => $b->reason,
                    'created_at' => $b->created_at?->toIso8601String(),
                ])->values(),
                'notifications'  => $user->userNotifications->map(fn ($n) => $n->toArray())->values(),
                'activity_logs'  => $userLogs,
                'exported_at'    => now()->toIso8601String(),
            ];

            $zip->addFromString(
                "user_{$user->id}_{$user->name}.json",
                json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );
        }

        $zip->close();

        return response()->download($zipPath, 'users_export_' . now()->format('Y-m-d') . '.zip')->deleteFileAfterSend(true);
    }

    public function importForm()
    {
        return view('admin.users.import');
    }

    public function importStore(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        // Skip header row
        fgetcsv($handle);

        $imported = 0;
        $skipped  = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 4) {
                continue;
            }

            [$name, $email, $password, $roleId] = array_map('trim', $row);

            if (User::where('email', $email)->exists()) {
                $skipped++;
                continue;
            }

            $role = Role::find($roleId);

            User::create([
                'name'     => $name,
                'email'    => $email,
                'password' => Hash::make($password),
                'role_id'  => $role ? $role->id : null,
                'is_admin' => $role && $role->is_admin_role,
            ]);

            $imported++;
        }

        fclose($handle);

        LogHelper::log('admin_imported_users', 'user', null, ['imported' => $imported, 'skipped' => $skipped]);

        return back()->with('success', "{$imported} utilisateur(s) importe(s), {$skipped} ignore(s) (doublons).");
    }
}
