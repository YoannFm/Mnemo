<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NavItem extends Model
{
    protected $fillable = ['label', 'url', 'icon', 'position', 'is_active', 'open_new_tab'];

    protected $casts = [
        'is_active' => 'boolean',
        'open_new_tab' => 'boolean',
        'position' => 'integer',
    ];
}
