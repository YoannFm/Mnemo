<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleReport extends Model
{
    protected $fillable = ['module_id', 'user_id', 'reason', 'note', 'status'];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
