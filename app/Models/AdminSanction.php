<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminSanction extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'admin_id', 'reason', 'type'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
