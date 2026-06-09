<?php

namespace Plugins\Reminders;

use App\Extensions\Plugin\BasePluginServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class RemindersServiceProvider extends BasePluginServiceProvider
{
    public function boot(): void
    {
        $this->commands([\Plugins\Reminders\Console\SendReminders::class]);
        $this->registerSchedule();
    }

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('reminders:send')->dailyAt('08:00');
    }
}
