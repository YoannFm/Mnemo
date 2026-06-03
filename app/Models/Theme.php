<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    protected $fillable = [
        'name', 'slug', 'is_active',
        'body_bg', 'content_bg', 'card_bg',
        'accent_color', 'header_bg', 'text_color',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
