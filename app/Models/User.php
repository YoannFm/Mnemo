<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Traits\TwoFactorAuthenticatable;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'two_factor_secret', 'two_factor_recovery_codes', 'is_admin', 'role_id', 'is_banned', 'banned_at', 'last_login_at', 'last_login_ip', 'force_password_change', 'accent_color'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

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
            'is_admin' => 'boolean',
            'is_banned' => 'boolean',
            'is_banned' => 'boolean',
            'banned_at' => 'datetime',
            'last_login_at' => 'datetime',
            'force_password_change' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (is_null($user->role_id)) {
                try {
                    $defaultRole = Role::where('is_admin_role', false)->orderBy('power')->first();
                    if ($defaultRole) {
                        $user->role_id = $defaultRole->id;
                    }
                } catch (\Throwable) {
                    // table may not exist during installation
                }
            }
        });
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
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

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function bans()
    {
        return $this->hasMany(Ban::class);
    }

    public function latestBan()
    {
        return $this->hasOne(Ban::class)->latestOfMany();
    }

    public function userNotifications()
    {
        return $this->hasMany(UserNotification::class)->latest();
    }
}
