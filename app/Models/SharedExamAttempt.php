<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SharedExamAttempt extends Model
{
    protected $fillable = [
        'shared_exam_id',
        'user_id',
        'guest_name',
        'answers',
        'score',
        'total',
        'finished_at',
        'results_sent_at',
    ];

    protected $casts = [
        'answers'          => 'array',
        'finished_at'      => 'datetime',
        'results_sent_at'  => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sharedExam(): BelongsTo
    {
        return $this->belongsTo(SharedExam::class);
    }

    public function getPercentageAttribute(): int
    {
        if ($this->total === 0) {
            return 0;
        }

        return (int) round(($this->score / $this->total) * 100);
    }
}
