<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    public $timestamps = false;
    protected $fillable = ['user_id', 'action', 'target_type', 'target_id', 'data'];

    protected function casts(): array
    {
        return ['data' => 'array', 'created_at' => 'datetime'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getActionFormat(): array
    {
        return match(true) {
            str_starts_with($this->action, 'created') => ['color' => 'success', 'icon' => 'plus-circle'],
            str_starts_with($this->action, 'updated') => ['color' => 'warning', 'icon' => 'pencil'],
            str_starts_with($this->action, 'deleted') => ['color' => 'danger', 'icon' => 'trash'],
            str_starts_with($this->action, 'banned')  => ['color' => 'danger', 'icon' => 'slash-circle'],
            str_starts_with($this->action, 'unbanned')=> ['color' => 'info',   'icon' => 'check-circle'],
            default => ['color' => 'secondary', 'icon' => 'circle'],
        };
    }

    public function getActionMessage(): string
    {
        $labels = [
            'created_user'    => 'Utilisateur créé',
            'updated_user'    => 'Utilisateur modifié',
            'deleted_user'    => 'Utilisateur supprimé',
            'banned_user'     => 'Utilisateur banni',
            'unbanned_user'   => 'Utilisateur débanni',
            'created_page'    => 'Page créée',
            'updated_page'    => 'Page modifiée',
            'deleted_page'    => 'Page supprimée',
            'created_post'    => 'Article créé',
            'updated_post'    => 'Article modifié',
            'deleted_post'    => 'Article supprimé',
            'created_role'    => 'Rôle créé',
            'updated_role'    => 'Rôle modifié',
            'deleted_role'    => 'Rôle supprimé',
            'created_image'   => 'Image ajoutée',
            'deleted_image'   => 'Image supprimée',
            'created_redirect'=> 'Redirection créée',
            'updated_redirect'=> 'Redirection modifiée',
            'deleted_redirect'=> 'Redirection supprimée',
        ];
        return $labels[$this->action] ?? $this->action;
    }
}
