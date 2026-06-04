<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PostComment extends Model
{
    protected $fillable = ['post_id', 'user_id', 'content', 'is_deleted', 'edited_at', 'parent_id'];

    protected function casts(): array {
        return ['is_deleted' => 'boolean', 'edited_at' => 'datetime'];
    }

    public function user() { return $this->belongsTo(User::class); }
    public function post() { return $this->belongsTo(Post::class); }
    public function replies() { return $this->hasMany(PostComment::class, 'parent_id')->with('user')->orderBy('created_at'); }
    public function parent() { return $this->belongsTo(PostComment::class, 'parent_id'); }
}
