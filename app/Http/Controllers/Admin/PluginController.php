<?php

namespace App\Http\Controllers\Admin;

use App\Extensions\PluginManager;
use App\Helpers\LogHelper;
use App\Http\Controllers\Controller;
use Throwable;

class PluginController extends Controller
{
    public function __construct(private PluginManager $plugins) {}

    public function index()
    {
        return view('admin.plugins.index', [
            'installed'  => $this->plugins->findPluginsDescriptions(),
            'available'  => collect($this->plugins->getAvailablePlugins()),
            'updates'    => $this->plugins->getPluginsToUpdate(),
        ]);
    }

    public function reload()
    {
        $this->plugins->getAvailablePlugins(force: true);
        return back()->with('success', 'Liste des plugins actualisée.');
    }

    public function enable(string $plugin)
    {
        try {
            $this->plugins->enable($plugin);
            $this->plugins->purgeCache();
            LogHelper::log('plugin_enabled', 'plugin', null, ['plugin' => $plugin]);
            return back()->with('success', 'Plugin activé.');
        } catch (Throwable $t) {
            return back()->with('error', $t->getMessage());
        }
    }

    public function disable(string $plugin)
    {
        try {
            $this->plugins->disable($plugin);
            $this->plugins->purgeCache();
            LogHelper::log('plugin_disabled', 'plugin', null, ['plugin' => $plugin]);
            return back()->with('success', 'Plugin désactivé.');
        } catch (Throwable $t) {
            return back()->with('error', $t->getMessage());
        }
    }

    public function install(string $slug)
    {
        try {
            $this->plugins->install($slug);
            LogHelper::log('plugin_installed', 'plugin', null, ['plugin' => $slug]);
            return back()->with('success', 'Plugin installé avec succès.');
        } catch (Throwable $t) {
            return back()->with('error', $t->getMessage());
        }
    }

    public function update(string $plugin)
    {
        try {
            $this->plugins->install($plugin);
            $this->plugins->purgeCache();
            LogHelper::log('plugin_updated', 'plugin', null, ['plugin' => $plugin]);
            return back()->with('success', 'Plugin mis à jour.');
        } catch (Throwable $t) {
            return back()->with('error', $t->getMessage());
        }
    }

    public function delete(string $plugin)
    {
        try {
            $this->plugins->delete($plugin);
            LogHelper::log('plugin_deleted', 'plugin', null, ['plugin' => $plugin]);
            return back()->with('success', 'Plugin supprimé.');
        } catch (Throwable $t) {
            return back()->with('error', $t->getMessage());
        }
    }
}
