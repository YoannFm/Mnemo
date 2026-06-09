<?php

namespace Plugins\Onboarding;

use App\Extensions\Plugin\BasePluginServiceProvider;
use Illuminate\Support\Facades\Route;

class OnboardingServiceProvider extends BasePluginServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrations();
        $this->loadViews();

        $this->app['view']->composer('dashboard', function ($view) {
            if (!auth()->check()) return;
            $user = auth()->user();
            if (!$user->onboarding_seen) {
                $view->with('showOnboarding', true);
            }
        });

        Route::middleware(['web', 'auth'])->group(function () {
            Route::post('/onboarding/dismiss', function () {
                $user = auth()->user();
                $user->onboarding_seen = true;
                $user->save();
                return response()->json(['ok' => true]);
            });
        });
    }
}
