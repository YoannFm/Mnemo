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
    }

    public function boot(): void
    {
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
