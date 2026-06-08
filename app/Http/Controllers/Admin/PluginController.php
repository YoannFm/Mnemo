<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PluginController extends Controller
{
    public function index()
    {
        return view('admin.plugins.index');
    }

    public function reload()
    {
        return back()->with('success', 'Plugins rechargés.');
    }

    public function enable(string $plugin)
    {
        return back()->with('success', 'Plugin activé.');
    }

    public function disable(string $plugin)
    {
        return back()->with('success', 'Plugin désactivé.');
    }

    public function install(string $slug)
    {
        return back()->with('success', 'Plugin installé.');
    }

    public function update(string $plugin)
    {
        return back()->with('success', 'Plugin mis à jour.');
    }

    public function delete(string $plugin)
    {
        return back()->with('success', 'Plugin supprimé.');
    }
}
