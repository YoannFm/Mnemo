<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(\App\Extensions\UpdateManager::class, function ($app) {
            return new \App\Extensions\UpdateManager($app['files']);
        });

        $this->app->singleton('plugins', function ($app) {
            return new \App\Extensions\Plugin\PluginManager($app['files']);
        });

        $this->app->alias('plugins', \App\Extensions\Plugin\PluginManager::class);
    }

    public function boot(): void
    {
        // Appliquer le fuseau horaire configuré en base dès le boot
        try {
            $tz = \App\Models\Setting::get('timezone', config('app.timezone', 'UTC'));
            if ($tz && in_array($tz, timezone_identifiers_list())) {
                config(['app.timezone' => $tz]);
                date_default_timezone_set($tz);
                Carbon::setTimezone($tz);
            }
        } catch (\Throwable) {}

        $this->app->make('plugins')->loadPlugins();

        Paginator::useBootstrapFive();
        Carbon::setLocale('fr');

        view()->composer('*', function ($view) {
            try {
                $theme = \App\Models\Theme::where('is_active', true)->first();
                $view->with('activeTheme', $theme);
            } catch (\Throwable) {}
        });

        view()->composer('layouts.app', function ($view) {
            if (auth()->check()) {
                try {
                    $unreadCount = \App\Models\UserNotification::where('user_id', auth()->id())
                        ->whereNull('read_at')
                        ->count();
                    $view->with('unreadNotifications', $unreadCount);
                } catch (\Throwable) {
                    $view->with('unreadNotifications', 0);
                }
            }
        });

        // Supprimer install.php si l'app est installee (securite apres git pull)
        if (env('APP_INSTALLED') === 'true') {
            $installFile = public_path('install.php');
            if (file_exists($installFile)) {
                @unlink($installFile);
            }
        }
    }
}
