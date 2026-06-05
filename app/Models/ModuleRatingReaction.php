<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleRatingReaction extends Model
{
    protected $fillable = ['module_rating_id', 'user_id', 'emoji'];

    public function moduleRating()
    {
        return $this->belongsTo(ModuleRating::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
