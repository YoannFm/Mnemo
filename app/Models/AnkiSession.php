<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnkiSession extends Model
{
    protected $fillable = [
        'user_id',
        'module_id',
        'mode',
        'learn_remaining',
        'learn_total',
    ];

    protected $casts = [
        'learn_remaining' => 'array',
        'learn_total'     => 'integer',
    ];

    public function isLearnMode(): bool
    {
        return $this->learn_remaining !== null;
    }
}
