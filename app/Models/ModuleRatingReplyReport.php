<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleRatingReplyReport extends Model
{
    protected $fillable = ['module_rating_reply_id', 'user_id', 'reason', 'note', 'status'];

    public function reply()
    {
        return $this->belongsTo(ModuleRatingReply::class, 'module_rating_reply_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
