<?php

namespace App\Http\Controllers\Admin;

use App\Extensions\Plugin\PluginManager;
use App\Http\Controllers\Controller;
use Throwable;

class PluginController extends Controller
{
    public function __construct(private PluginManager $plugins) {}

    public function index()
    {
        $installed = $this->plugins->discoverPlugins()->map(function ($plugin) {
            $plugin->is_enabled = $this->plugins->isEnabled($plugin->id);
            return $plugin;
        });

        try {
            $available = $this->plugins->getAvailablePlugins();
        } catch (Throwable) {
            $available = collect();
        }

        // Build a map of slug => latest_version from cloud data
        $cloudVersions = $available->keyBy('slug')->map(fn ($p) => $p->latest_version ?? null);

        $installed = $installed->map(function ($plugin) use ($cloudVersions) {
            $cloud = $cloudVersions->get($plugin->id);
            $plugin->has_update = $cloud && version_compare($cloud, $plugin->version ?? '0', '>');
            $plugin->latest_version = $cloud;
            return $plugin;
        });

        return view('admin.plugins.index', compact('installed', 'available'));
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
        $installed = $this->plugins->discoverPlugins()->pluck('id')->map('strtolower')->toArray();
        if (in_array(strtolower($slug), $installed) || is_dir(base_path("plugins/{$slug}"))) {
            return back()->with('error', 'Ce plugin est déjà installé.');
        }

        try {
            $this->plugins->install($slug);
            return back()->with('success', 'Plugin installé. Vous pouvez maintenant l\'activer.');
        } catch (Throwable $t) {
            return back()->with('error', $t->getMessage());
        }
    }

    public function update(string $plugin)
    {
        try {
            $this->plugins->install($plugin);
            return back()->with('success', 'Plugin mis à jour.');
        } catch (Throwable $t) {
            return back()->with('error', $t->getMessage());
        }
    }

    public function delete(string $plugin)
    {
        $this->plugins->delete($plugin);
        return back()->with('success', 'Plugin supprimé.');
    }
}
