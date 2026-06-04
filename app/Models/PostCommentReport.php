<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PostCommentReport extends Model
{
    protected $fillable = ['post_comment_id', 'user_id'];
}
