<?php

namespace App\Helpers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class LogHelper
{
    public static function log(
        string $action,
        ?string $targetType = null,
        ?int $targetId = null,
        ?array $data = null,
        string $level = 'info',
        ?string $oldValue = null,
        ?string $newValue = null
    ): void {
        ActivityLog::create([
            'user_id'     => Auth::id(),
            'action'      => $action,
            'target_type' => $targetType,
            'target_id'   => $targetId,
            'data'        => $data,
            'level'       => $level,
            'old_value'   => $oldValue,
            'new_value'   => $newValue,
            'created_at'  => now()->utc(),
        ]);
    }
}
