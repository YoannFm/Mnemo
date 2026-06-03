<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'name', 'color', 'power', 'is_admin_role',
        'can_create_module', 'can_train_own', 'can_test_own',
        'can_access_library', 'can_train_public', 'can_test_public',
    ];

    protected function casts(): array
    {
        return [
            'is_admin_role'      => 'boolean',
            'can_create_module'  => 'boolean',
            'can_train_own'      => 'boolean',
            'can_test_own'       => 'boolean',
            'can_access_library' => 'boolean',
            'can_train_public'   => 'boolean',
            'can_test_public'    => 'boolean',
        ];
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
