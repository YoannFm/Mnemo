<?php

namespace Plugins\Streak\Listeners;

use App\Events\UserActivity;
use Plugins\Streak\Models\UserStreak;

class RecordStreak
{
    public function handle(UserActivity $event): void
    {
        $user = $event->user;
        $today = now()->toDateString();

        $streak = UserStreak::firstOrCreate(
            ['user_id' => $user->id],
            ['current_streak' => 0, 'longest_streak' => 0, 'last_activity_date' => null]
        );

        $lastDate = $streak->last_activity_date?->toDateString();

        if ($lastDate === $today) {
            return;
        }

        $yesterday = now()->subDay()->toDateString();

        if ($lastDate === $yesterday) {
            $streak->current_streak += 1;
        } else {
            $streak->current_streak = 1;
        }

        $streak->longest_streak = max($streak->longest_streak, $streak->current_streak);
        $streak->last_activity_date = $today;
        $streak->save();
    }
}
