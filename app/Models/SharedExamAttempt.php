<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SharedExamAttempt extends Model
{
    protected $fillable = [
        'shared_exam_id',
        'guest_name',
        'answers',
        'score',
        'total',
        'finished_at',
    ];

    protected $casts = [
        'answers'     => 'array',
        'finished_at' => 'datetime',
    ];

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
