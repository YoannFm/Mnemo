<?php

namespace App\Http\Controllers\Admin;

use App\Extensions\Plugin\PluginManager;
use App\Helpers\LogHelper;
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

        $available = $this->plugins->getAvailablePlugins();

        // Version proposée par le catalogue pour chaque plugin (slug => version)
        $catalogVersions = $available->pluck('version', 'slug');

        $installed = $installed->map(function ($plugin) use ($catalogVersions) {
            $latest = $catalogVersions->get($plugin->id);
            $plugin->has_update = $latest && version_compare($latest, $plugin->version ?? '0', '>');
            $plugin->latest_version = $latest;
            return $plugin;
        });

        return view('admin.plugins.index', compact('installed', 'available'));
    }

    public function reload()
    {
        LogHelper::log('reloaded_plugins', 'plugin', null);
        return back()->with('success', 'Plugins rechargés.');
    }

    public function enable(string $plugin)
    {
        $this->plugins->enable($plugin);
        LogHelper::log('enabled_plugin', 'plugin', null, ['plugin' => $plugin]);
        return back()->with('success', 'Plugin activé.');
    }

    public function disable(string $plugin)
    {
        $this->plugins->disable($plugin);
        LogHelper::log('disabled_plugin', 'plugin', null, ['plugin' => $plugin], 'warning');
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
            LogHelper::log('installed_plugin', 'plugin', null, ['plugin' => $slug]);
            return back()->with('success', 'Plugin installé. Vous pouvez maintenant l\'activer.');
        } catch (Throwable $t) {
            return back()->with('error', $t->getMessage());
        }
    }

    public function update(string $plugin)
    {
        try {
            $this->plugins->install($plugin);
            LogHelper::log('updated_plugin', 'plugin', null, ['plugin' => $plugin]);
            return back()->with('success', 'Plugin mis à jour.');
        } catch (Throwable $t) {
            return back()->with('error', $t->getMessage());
        }
    }

    public function delete(string $plugin)
    {
        $this->plugins->delete($plugin);
        LogHelper::log('deleted_plugin', 'plugin', null, ['plugin' => $plugin], 'warning');
        return back()->with('success', 'Plugin supprimé.');
    }
}
