<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = ['title', 'description', 'image', 'slug', 'content', 'user_id', 'published_at', 'is_pinned'];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'is_pinned'    => 'boolean',
        ];
    }

    public function imageUrl(): string
    {
        return asset('storage/' . $this->image);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
