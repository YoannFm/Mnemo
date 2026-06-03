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
        $role = new Role();
        return view('admin.roles.create', compact('role'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'               => 'required|string|max:50',
            'color'              => 'required|string|max:7',
            'icon'               => 'nullable|string|max:50',
            'power'              => 'required|integer|min:0',
            'is_admin_role'      => 'sometimes|boolean',
            'can_create_module'  => 'sometimes|boolean',
            'can_train_own'      => 'sometimes|boolean',
            'can_test_own'       => 'sometimes|boolean',
            'can_access_library' => 'sometimes|boolean',
            'can_train_public'   => 'sometimes|boolean',
            'can_test_public'    => 'sometimes|boolean',
        ]);
        $data['is_admin_role']      = $request->boolean('is_admin_role');
        $data['can_create_module']  = $request->boolean('can_create_module');
        $data['can_train_own']      = $request->boolean('can_train_own');
        $data['can_test_own']       = $request->boolean('can_test_own');
        $data['can_access_library'] = $request->boolean('can_access_library');
        $data['can_train_public']   = $request->boolean('can_train_public');
        $data['can_test_public']    = $request->boolean('can_test_public');

        $role = Role::create($data);
        LogHelper::log('created_role', 'role', $role->id, ['name' => $role->name]);
        return redirect()->route('admin.roles.index')->with('success', 'Role cree.');
    }

    public function edit(Role $role)
    {
        return view('admin.roles.edit', compact('role'));
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name'               => 'required|string|max:50',
            'color'              => 'required|string|max:7',
            'icon'               => 'nullable|string|max:50',
            'power'              => 'required|integer|min:0',
            'is_admin_role'      => 'sometimes|boolean',
            'can_create_module'  => 'sometimes|boolean',
            'can_train_own'      => 'sometimes|boolean',
            'can_test_own'       => 'sometimes|boolean',
            'can_access_library' => 'sometimes|boolean',
            'can_train_public'   => 'sometimes|boolean',
            'can_test_public'    => 'sometimes|boolean',
        ]);
        $data['is_admin_role']      = $request->boolean('is_admin_role');
        $data['can_create_module']  = $request->boolean('can_create_module');
        $data['can_train_own']      = $request->boolean('can_train_own');
        $data['can_test_own']       = $request->boolean('can_test_own');
        $data['can_access_library'] = $request->boolean('can_access_library');
        $data['can_train_public']   = $request->boolean('can_train_public');
        $data['can_test_public']    = $request->boolean('can_test_public');

        $role->update($data);
        LogHelper::log('updated_role', 'role', $role->id, ['name' => $role->name]);
        return redirect()->route('admin.roles.index')->with('success', 'Role mis a jour.');
    }

    public function destroy(Role $role)
    {
        LogHelper::log('deleted_role', 'role', $role->id, ['name' => $role->name]);
        $role->delete();
        return redirect()->route('admin.roles.index')->with('success', 'Role supprime.');
    }

    public function updateOrder(Request $request)
    {
        $order = $request->input('order', []);
        foreach ($order as $index => $id) {
            Role::where('id', $id)->update(['power' => count($order) - $index]);
        }
        return response()->json(['success' => true]);
    }
}
