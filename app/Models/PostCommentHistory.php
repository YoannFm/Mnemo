<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostCommentHistory extends Model
{
    protected $table = 'post_comment_history';
    public $timestamps = false;

    protected $fillable = ['post_comment_id', 'content'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function postComment()
    {
        return $this->belongsTo(PostComment::class);
    }
}
