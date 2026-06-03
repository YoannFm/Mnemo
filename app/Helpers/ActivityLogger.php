<?php

namespace App\Helpers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public static function log(string $action, ?string $targetType = null, ?int $targetId = null, ?array $data = null): void
    {
        $userId = Auth::id();
        if (! $userId) {
            return;
        }

        ActivityLog::create([
            'user_id'     => $userId,
            'action'      => $action,
            'target_type' => $targetType,
            'target_id'   => $targetId,
            'data'        => $data,
            'created_at'  => now(),
        ]);
    }
}
