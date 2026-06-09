<?php

namespace Plugins\Streak;

use App\Events\UserActivity;
use App\Extensions\Plugin\BasePluginServiceProvider;
use Plugins\Streak\Listeners\RecordStreak;
use Plugins\Streak\Models\UserStreak;

class StreakServiceProvider extends BasePluginServiceProvider
{
    public function boot(): void
    {
        $this->loadViews();
        $this->loadMigrations();

        $this->app['events']->listen(UserActivity::class, RecordStreak::class);

        $this->app['view']->composer('dashboard', function ($view) {
            if (!auth()->check()) return;

            $streak = UserStreak::firstOrCreate(
                ['user_id' => auth()->id()],
                ['current_streak' => 0, 'longest_streak' => 0]
            );

            $html = view('streak::widget', ['userStreak' => $streak])->render();
            $widgets = $view->getData()['pluginWidgets'] ?? [];
            $view->with('pluginWidgets', array_merge($widgets, [$html]));
        });
    }
}
