<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\LogHelper;
use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\User;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function index()
    {
        $modules = Module::with('owner')
            ->where('is_public', true)
            ->withCount('items')
            ->latest()
            ->paginate(20);

        return view('admin.modules.index', compact('modules'));
    }

    public function destroy(Module $module)
    {
        LogHelper::log('deleted_module', 'module', $module->id, ['title' => $module->title, 'owner_id' => $module->owner_id], 'warning');

        $module->delete();

        return back()->with('success', 'Module supprimé.');
    }

    public function trash(Request $request)
    {
        $query = Module::onlyTrashed()->with('owner')->withCount('items');

        if ($userId = $request->input('user_id')) {
            $query->where('owner_id', $userId);
        }

        if ($search = $request->input('search')) {
            $query->where('title', 'like', '%' . $search . '%');
        }

        $modules = $query->latest('deleted_at')->paginate(25)->withQueryString();
        $users = User::orderBy('name')->get();

        return view('admin.modules.trash', compact('modules', 'users'));
    }

    public function restore(int $id)
    {
        $module = Module::onlyTrashed()->findOrFail($id);
        $module->restore();

        LogHelper::log('restored_module', 'module', $module->id, ['title' => $module->title, 'owner_id' => $module->owner_id]);

        return back()->with('success', 'Module restauré.');
    }

    public function forceDelete(int $id)
    {
        $module = Module::onlyTrashed()->findOrFail($id);

        LogHelper::log('force_deleted_module', 'module', $module->id, ['title' => $module->title, 'owner_id' => $module->owner_id], 'warning');

        $module->forceDelete();

        return back()->with('success', 'Module supprimé définitivement.');
    }
}
