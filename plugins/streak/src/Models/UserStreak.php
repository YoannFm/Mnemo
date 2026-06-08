<?php

namespace Plugins\Streak\Models;

use Illuminate\Database\Eloquent\Model;

class UserStreak extends Model
{
    protected $table = 'user_streaks';

    protected $fillable = [
        'user_id',
        'current_streak',
        'longest_streak',
        'last_activity_date',
    ];

    protected function casts(): array
    {
        return [
            'last_activity_date' => 'date',
        ];
    }
}
