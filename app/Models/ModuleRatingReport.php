<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleRatingReport extends Model
{
    protected $fillable = ['module_rating_id', 'user_id', 'reason', 'note', 'status'];

    public function moduleRating()
    {
        return $this->belongsTo(ModuleRating::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
