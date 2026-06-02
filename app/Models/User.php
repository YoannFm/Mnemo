<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ─────────────────────────────────────────────
    // Relations Eloquent
    // ─────────────────────────────────────────────

    /**
     * Un utilisateur possède plusieurs modules (via owner_id).
     */
    public function modules()
    {
        return $this->hasMany(Module::class, 'owner_id');
    }

    /**
     * Un utilisateur a une entrée de progression par item pratiqué.
     */
    public function progresses()
    {
        return $this->hasMany(Progress::class);
    }

    /**
     * Un utilisateur a un historique de scores de ses sessions de test.
     */
    public function scores()
    {
        return $this->hasMany(Score::class);
    }
}
