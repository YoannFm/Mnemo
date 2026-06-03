<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['name', 'color', 'power', 'is_admin_role'];

    protected function casts(): array
    {
        return ['is_admin_role' => 'boolean'];
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function getBadgeStyle(): string
    {
        return "background-color: {$this->color}; color: #fff;";
    }
}
