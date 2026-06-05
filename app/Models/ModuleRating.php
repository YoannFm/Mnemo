<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleRating extends Model
{
    protected $fillable = ['user_id', 'module_id', 'rating', 'comment'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function reports()
    {
        return $this->hasMany(ModuleRatingReport::class);
    }

    public function reactions()
    {
        return $this->hasMany(ModuleRatingReaction::class);
    }

    public function replies()
    {
        return $this->hasMany(ModuleRatingReply::class);
    }
}
