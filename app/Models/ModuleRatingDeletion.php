<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleRatingDeletion extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'module_id', 'type', 'rating', 'content', 'deleted_by'];

    protected $casts = ['created_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
