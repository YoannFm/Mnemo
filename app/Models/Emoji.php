<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Emoji extends Model
{
    protected $fillable = ['name', 'slug', 'type', 'image_path'];

    public function imageUrl(): string
    {
        return asset('storage/' . $this->image_path);
    }
}
