<?php

namespace App\Extensions\Plugin;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

abstract class BasePluginServiceProvider extends ServiceProvider
{
    use HasPlugin;

    protected array $routeMiddleware = [];
    protected array $policies = [];
    protected ?Router $router;

    public function __construct(Application $app)
    {
        parent::__construct($app);
        $this->router = $app[Router::class];
    }

    public function register(): void {}

    protected function schedule(Schedule $schedule): void {}

    protected function loadViews(): void
    {
        $path = $this->pluginPath('resources/views');
        if (is_dir($path)) {
            $this->loadViewsFrom($path, $this->plugin->id);
        }
    }

    protected function loadMigrations(): void
    {
        $path = $this->pluginPath('database/migrations');
        if (is_dir($path)) {
            $this->loadMigrationsFrom($path);
        }
    }

    protected function loadTranslations(): void
    {
        $path = $this->pluginPath('resources/lang');
        if (is_dir($path)) {
            $this->loadTranslationsFrom($path, $this->plugin->id);
        }
    }

    protected function registerAdminNavigation(): void
    {
        $this->app['plugins']->addAdminNavItem(fn () => $this->adminNavigation());
    }

    protected function registerUserNavigation(): void
    {
        $this->app['plugins']->addUserNavItem(fn () => $this->userNavigation());
    }

    protected function registerSchedule(): void
    {
        if ($this->app->runningInConsole()) {
            $this->app->booted(function () {
                $this->schedule($this->app->make(Schedule::class));
            });
        }
    }

    protected function adminNavigation(): array
    {
        return [];
    }

    protected function userNavigation(): array
    {
        return [];
    }

    protected function pluginPath(string $path = ''): string
    {
        return $this->app['plugins']->path($this->plugin->id, $path);
    }

    protected function pluginResourcePath(string $path = ''): string
    {
        return $this->pluginPath('resources/'.$path);
    }
}
