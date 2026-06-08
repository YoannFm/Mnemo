<?php

namespace App\Providers;

use App\Extensions\PluginManager;
use Illuminate\Support\ServiceProvider;

class PluginServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PluginManager::class, function ($app) {
            return new PluginManager($app['files']);
        });

        $this->app->alias(PluginManager::class, 'plugins');
    }

    public function boot(): void
    {
        try {
            $this->app[PluginManager::class]->loadPlugins($this->app);
        } catch (\Throwable $t) {
            if (!$this->app->isProduction()) {
                throw $t;
            }
            report($t);
        }
    }
}
