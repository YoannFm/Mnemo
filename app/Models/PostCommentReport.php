<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PostCommentReport extends Model
{
    protected $fillable = ['post_comment_id', 'user_id', 'reason', 'note', 'status'];

    public function user() { return $this->belongsTo(User::class); }
    public function postComment() { return $this->belongsTo(PostComment::class); }
}
