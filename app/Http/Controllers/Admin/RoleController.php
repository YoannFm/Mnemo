<?php
namespace App\Http\Controllers\Admin;

use App\Helpers\LogHelper;
use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::orderBy('power', 'desc')->get();
        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        return view('admin.roles.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:50',
            'color'        => 'required|string|max:7',
            'power'        => 'required|integer|min:0',
            'is_admin_role'=> 'sometimes|boolean',
        ]);
        $data['is_admin_role'] = $request->boolean('is_admin_role');
        $role = Role::create($data);
        LogHelper::log('created_role', 'role', $role->id, ['name' => $role->name]);
        return redirect()->route('admin.roles.index')->with('success', 'Rôle créé.');
    }

    public function edit(Role $role)
    {
        return view('admin.roles.edit', compact('role'));
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:50',
            'color'        => 'required|string|max:7',
            'power'        => 'required|integer|min:0',
            'is_admin_role'=> 'sometimes|boolean',
        ]);
        $data['is_admin_role'] = $request->boolean('is_admin_role');
        $role->update($data);
        LogHelper::log('updated_role', 'role', $role->id, ['name' => $role->name]);
        return redirect()->route('admin.roles.index')->with('success', 'Rôle mis à jour.');
    }

    public function destroy(Role $role)
    {
        LogHelper::log('deleted_role', 'role', $role->id, ['name' => $role->name]);
        $role->delete();
        return redirect()->route('admin.roles.index')->with('success', 'Rôle supprimé.');
    }
}
