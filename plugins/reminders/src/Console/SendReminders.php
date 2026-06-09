<?php

namespace Plugins\Reminders\Console;

use App\Models\Progress;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Console\Command;

class SendReminders extends Command
{
    protected $signature = 'reminders:send';
    protected $description = 'Envoie des rappels de révision aux utilisateurs inactifs';

    public function handle(): void
    {
        // Users who have email_notifications enabled and haven't studied in 3+ days
        // Uses last_seen field on Progress to track last study activity
        $users = User::where('email_notifications', true)
            ->whereHas('progresses', function ($q) {
                $q->where('last_seen', '<=', now()->subDays(3));
            })
            ->get();

        foreach ($users as $user) {
            // Count items due for review (streak < 3 or fail_count > 0)
            $dueCount = Progress::where('user_id', $user->id)
                ->where(function ($q) {
                    $q->where('streak', '<', 3)->orWhere('fail_count', '>', 0);
                })
                ->count();

            if ($dueCount === 0) continue;

            UserNotification::create([
                'user_id' => $user->id,
                'title'   => 'Il est temps de réviser !',
                'message' => "Tu as {$dueCount} item(s) à réviser. Lance une session Anki pour maintenir ta progression.",
                'type'    => 'info',
            ]);
        }

        $this->info('Rappels envoyés.');
    }
}
