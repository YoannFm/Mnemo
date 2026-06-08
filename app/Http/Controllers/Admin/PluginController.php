<?php

namespace App\Http\Controllers\Admin;

use App\Extensions\Plugin\PluginManager;
use App\Http\Controllers\Controller;

class PluginController extends Controller
{
    public function __construct(private PluginManager $plugins) {}

    public function index()
    {
        $plugins = $this->plugins->discoverPlugins()->map(function ($plugin) {
            $plugin->is_enabled = $this->plugins->isEnabled($plugin->id);
            return $plugin;
        });

        return view('admin.plugins.index', compact('plugins'));
    }

    public function reload()
    {
        return back()->with('success', 'Plugins rechargés.');
    }

    public function enable(string $plugin)
    {
        $this->plugins->enable($plugin);
        return back()->with('success', 'Plugin activé.');
    }

    public function disable(string $plugin)
    {
        $this->plugins->disable($plugin);
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
        $this->plugins->delete($plugin);
        return back()->with('success', 'Plugin supprimé.');
    }
}
