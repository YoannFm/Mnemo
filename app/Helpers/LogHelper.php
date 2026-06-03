<?php
namespace App\Helpers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class LogHelper
{
    public static function log(string $action, ?string $targetType = null, ?int $targetId = null, ?array $data = null): void
    {
        ActivityLog::create([
            'user_id'     => Auth::id(),
            'action'      => $action,
            'target_type' => $targetType,
            'target_id'   => $targetId,
            'data'        => $data,
        ]);
    }
}
