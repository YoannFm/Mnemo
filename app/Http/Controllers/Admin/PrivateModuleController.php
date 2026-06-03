<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\User;
use Illuminate\Http\Request;

class PrivateModuleController extends Controller
{
    public function index(Request $request)
    {
        $query = Module::where('is_public', false)->with('owner');

        if ($userId = $request->input('user_id')) {
            $query->where('owner_id', $userId);
        }

        $modules = $query->latest()->paginate(25)->withQueryString();
        $users = User::orderBy('name')->get();

        return view('admin.private-modules.index', compact('modules', 'users'));
    }

    public function destroy(Module $module)
    {
        $module->delete();
        return back()->with('success', 'Module prive supprime.');
    }
}
