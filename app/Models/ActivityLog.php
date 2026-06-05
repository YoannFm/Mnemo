<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    public $timestamps = false;
    protected $fillable = ['user_id', 'action', 'target_type', 'target_id', 'data', 'level', 'old_value', 'new_value'];

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
            str_starts_with($this->action, 'created')      => ['color' => 'success', 'icon' => 'plus-circle'],
            str_starts_with($this->action, 'updated')      => ['color' => 'warning', 'icon' => 'pencil'],
            str_starts_with($this->action, 'deleted')      => ['color' => 'danger',  'icon' => 'trash'],
            str_starts_with($this->action, 'banned')       => ['color' => 'danger',  'icon' => 'slash-circle'],
            str_starts_with($this->action, 'unbanned')     => ['color' => 'info',    'icon' => 'check-circle'],
            str_starts_with($this->action, 'muted')        => ['color' => 'warning', 'icon' => 'mic-mute'],
            str_starts_with($this->action, 'transferred')  => ['color' => 'info',    'icon' => 'arrow-left-right'],
            str_starts_with($this->action, 'treated')      => ['color' => 'success', 'icon' => 'check2-circle'],
            str_starts_with($this->action, 'rejected')     => ['color' => 'secondary','icon' => 'x-circle'],
            str_starts_with($this->action, 'sanctioned')   => ['color' => 'danger',  'icon' => 'exclamation-triangle'],
            str_starts_with($this->action, 'unsanctioned') => ['color' => 'success', 'icon' => 'shield-check'],
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
            'created_redirect'    => 'Redirection créée',
            'updated_redirect'    => 'Redirection modifiée',
            'deleted_redirect'    => 'Redirection supprimée',
            'deleted_module'      => 'Module supprimé',
            'transferred_module'  => 'Module transféré',
            'treated_module_report'  => 'Signalement module traité',
            'rejected_module_report' => 'Signalement module rejeté',
            'treated_reply_report'   => 'Signalement réponse traité',
            'rejected_reply_report'  => 'Signalement réponse rejeté',
            'deleted_reply'       => 'Réponse supprimée',
            'treated_rating_report'  => 'Signalement avis traité',
            'rejected_rating_report' => 'Signalement avis rejeté',
            'deleted_rating'      => 'Avis supprimé',
            'sanctioned_comment'  => 'Commentaire sanctionné',
            'unsanctioned_comment'=> 'Commentaire non sanctionné',
            'deleted_comment'     => 'Commentaire supprimé',
            'updated_comment'     => 'Commentaire modifié',
            'muted_user'          => 'Utilisateur muté',
            'updated_settings'    => 'Paramètres mis à jour',
        ];
        return $labels[$this->action] ?? $this->action;
    }
}
