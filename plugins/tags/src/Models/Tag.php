<?php

namespace Plugins\Tags\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = ['name', 'color'];

    public function modules()
    {
        return $this->belongsToMany(\App\Models\Module::class, 'module_tag');
    }
}
