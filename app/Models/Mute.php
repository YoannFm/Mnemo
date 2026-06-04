<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mute extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'admin_id', 'reason', 'expires_at'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'created_at' => 'datetime',
        ];
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
