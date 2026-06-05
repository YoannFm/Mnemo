<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleRatingReply extends Model
{
    protected $fillable = ['module_rating_id', 'user_id', 'content'];

    public function moduleRating()
    {
        return $this->belongsTo(ModuleRating::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reports()
    {
        return $this->hasMany(ModuleRatingReplyReport::class, 'module_rating_reply_id');
    }
}
