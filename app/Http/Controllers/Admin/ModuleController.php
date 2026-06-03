<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;

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
        $module->delete();

        return back()->with('success', 'Module supprimé.');
    }
}
