<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NavItem extends Model
{
    protected $fillable = ['label', 'icon', 'type', 'value', 'parent_id', 'new_tab', 'position', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'new_tab'   => 'boolean',
        'position'  => 'integer',
        'parent_id' => 'integer',
    ];

    public function children()
    {
        return $this->hasMany(NavItem::class, 'parent_id')->orderBy('position');
    }

    public function isDropdown(): bool
    {
        return $this->type === 'dropdown';
    }

    public function getUrl(): string
    {
        return match ($this->type) {
            'link'     => $this->value ?? '#',
            'page'     => route('page.show', $this->value ?? ''),
            'post'     => route('post.show', $this->value ?? ''),
            'dropdown' => '#',
            default    => $this->value ?? '#',
        };
    }
}
