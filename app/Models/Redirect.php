<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    protected $fillable = ['source', 'target', 'type', 'is_enabled'];

    protected function casts(): array
    {
        return ['is_enabled' => 'boolean', 'type' => 'integer'];
    }
}
