<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SharedExam extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'module_id',
        'mode',
        'label',
        'starts_at',
        'expires_at',
        'show_answers',
        'max_attempts',
        'webhook_url',
    ];

    protected $casts = [
        'starts_at'    => 'datetime',
        'expires_at'   => 'datetime',
        'show_answers' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(SharedExamAttempt::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isNotStarted(): bool
    {
        return $this->starts_at !== null && $this->starts_at->isFuture();
    }
}
