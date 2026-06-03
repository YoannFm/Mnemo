<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ban extends Model
{
    protected $fillable = ['user_id', 'banned_by', 'reason'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'banned_by');
    }
}
