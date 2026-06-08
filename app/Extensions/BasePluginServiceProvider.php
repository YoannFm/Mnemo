<?php

namespace App\Extensions;

use Illuminate\Support\ServiceProvider;

abstract class BasePluginServiceProvider extends ServiceProvider
{
    protected ?object $plugin = null;

    public function bindPlugin(object $plugin): void
    {
        $this->plugin = $plugin;
    }

    protected function pluginPath(string $path = ''): string
    {
        return app(PluginManager::class)->path($this->plugin->id, $path);
    }

    protected function loadPluginViews(): void
    {
        $this->loadViewsFrom($this->pluginPath('resources/views'), $this->plugin->id);
    }

    protected function loadPluginTranslations(): void
    {
        $this->loadTranslationsFrom($this->pluginPath('resources/lang'), $this->plugin->id);
    }

    protected function loadPluginMigrations(): void
    {
        $this->loadMigrationsFrom($this->pluginPath('database/migrations'));
    }

    protected function loadPluginRoutes(string $file = 'routes/web.php'): void
    {
        $path = $this->pluginPath($file);

        if (app(\Illuminate\Filesystem\Filesystem::class)->exists($path)) {
            $this->loadRoutesFrom($path);
        }
    }
}
