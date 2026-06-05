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
            $this->action === 'updated_setting'                    => ['color' => '#6366f1', 'icon' => 'gear-fill'],
            str_starts_with($this->action, 'created_nav')         => ['color' => '#0ea5e9', 'icon' => 'layout-text-sidebar'],
            str_starts_with($this->action, 'updated_nav')         => ['color' => '#0ea5e9', 'icon' => 'layout-text-sidebar'],
            str_starts_with($this->action, 'deleted_nav')         => ['color' => '#0ea5e9', 'icon' => 'layout-text-sidebar'],
            $this->action === 'reordered_nav'                     => ['color' => '#0ea5e9', 'icon' => 'layout-text-sidebar'],
            in_array($this->action, ['exported_user_data', 'exported_all_users', 'imported_users', 'exported_module', 'imported_module', 'imported_items_csv']) => ['color' => 'info', 'icon' => 'box-arrow-up'],
            // Post actions
            str_starts_with($this->action, 'created_post')        => ['color' => '#8b5cf6', 'icon' => 'bi-file-text-fill'],
            str_starts_with($this->action, 'updated_post')        => ['color' => '#8b5cf6', 'icon' => 'bi-file-text-fill'],
            str_starts_with($this->action, 'deleted_post')        => ['color' => '#8b5cf6', 'icon' => 'bi-file-text-fill'],
            // Image actions
            str_starts_with($this->action, 'created_image')       => ['color' => '#ec4899', 'icon' => 'bi-image-fill'],
            str_starts_with($this->action, 'updated_image')       => ['color' => '#ec4899', 'icon' => 'bi-image-fill'],
            str_starts_with($this->action, 'deleted_image')       => ['color' => '#ec4899', 'icon' => 'bi-image-fill'],
            // Emoji actions
            str_starts_with($this->action, 'created_emoji')       => ['color' => '#f59e0b', 'icon' => 'bi-emoji-smile-fill'],
            str_starts_with($this->action, 'updated_emoji')       => ['color' => '#f59e0b', 'icon' => 'bi-emoji-smile-fill'],
            str_starts_with($this->action, 'deleted_emoji')       => ['color' => '#f59e0b', 'icon' => 'bi-emoji-smile-fill'],
            str_starts_with($this->action, 'imported_emoji')      => ['color' => '#f59e0b', 'icon' => 'bi-emoji-smile-fill'],
            // Role actions
            str_starts_with($this->action, 'created_role')        => ['color' => '#6366f1', 'icon' => 'bi-shield-fill'],
            str_starts_with($this->action, 'updated_role')        => ['color' => '#6366f1', 'icon' => 'bi-shield-fill'],
            str_starts_with($this->action, 'deleted_role')        => ['color' => '#6366f1', 'icon' => 'bi-shield-fill'],
            // Ban actions
            str_starts_with($this->action, 'banned')              => ['color' => '#dc2626', 'icon' => 'bi-slash-circle-fill'],
            str_starts_with($this->action, 'unbanned')            => ['color' => '#dc2626', 'icon' => 'bi-slash-circle-fill'],
            // Sanction actions
            str_starts_with($this->action, 'created_sanction')    => ['color' => '#ea580c', 'icon' => 'bi-exclamation-triangle-fill'],
            str_starts_with($this->action, 'deleted_sanction')    => ['color' => '#ea580c', 'icon' => 'bi-exclamation-triangle-fill'],
            // Redirect actions
            str_starts_with($this->action, 'created_redirect')    => ['color' => '#0891b2', 'icon' => 'bi-signpost-fill'],
            str_starts_with($this->action, 'updated_redirect')    => ['color' => '#0891b2', 'icon' => 'bi-signpost-fill'],
            str_starts_with($this->action, 'deleted_redirect')    => ['color' => '#0891b2', 'icon' => 'bi-signpost-fill'],
            // Theme actions
            str_starts_with($this->action, 'created_theme')       => ['color' => '#7c3aed', 'icon' => 'bi-palette-fill'],
            str_starts_with($this->action, 'updated_theme')       => ['color' => '#7c3aed', 'icon' => 'bi-palette-fill'],
            str_starts_with($this->action, 'deleted_theme')       => ['color' => '#7c3aed', 'icon' => 'bi-palette-fill'],
            str_starts_with($this->action, 'activated_theme')     => ['color' => '#7c3aed', 'icon' => 'bi-palette-fill'],
            // Module actions
            str_starts_with($this->action, 'deleted_module')      => ['color' => '#64748b', 'icon' => 'bi-collection-fill'],
            str_starts_with($this->action, 'transferred_module')  => ['color' => '#64748b', 'icon' => 'bi-collection-fill'],
            // Report actions
            str_starts_with($this->action, 'treated_report')      => ['color' => '#16a34a', 'icon' => 'bi-flag-fill'],
            str_starts_with($this->action, 'rejected_report')     => ['color' => '#dc2626', 'icon' => 'bi-flag-fill'],
            // Notification actions
            $this->action === 'sent_notification'                  => ['color' => '#0284c7', 'icon' => 'bi-bell-fill'],
            $this->action === 'deleted_notification'               => ['color' => '#0284c7', 'icon' => 'bi-bell-fill'],
            // Generic fallbacks
            str_starts_with($this->action, 'created')             => ['color' => 'success', 'icon' => 'plus-circle'],
            str_starts_with($this->action, 'updated')             => ['color' => 'warning', 'icon' => 'pencil'],
            str_starts_with($this->action, 'deleted')             => ['color' => 'danger',  'icon' => 'trash'],
            $this->action === 'forced_password_change'            => ['color' => 'warning', 'icon' => 'key'],
            default => ['color' => 'secondary', 'icon' => 'circle'],
        };
    }

    public function getActionMessage(): string
    {
        $labels = [
            'created_user'         => 'Utilisateur créé',
            'updated_user'         => 'Utilisateur modifié',
            'deleted_user'         => 'Utilisateur supprimé',
            'banned_user'          => 'Utilisateur banni',
            'unbanned_user'        => 'Utilisateur débanni',
            'created_page'         => 'Page créée',
            'updated_page'         => 'Page modifiée',
            'deleted_page'         => 'Page supprimée',
            'created_post'         => 'Article créé',
            'updated_post'         => 'Article modifié',
            'deleted_post'         => 'Article supprimé',
            'created_role'         => 'Rôle créé',
            'updated_role'         => 'Rôle modifié',
            'deleted_role'         => 'Rôle supprimé',
            'created_image'        => 'Image ajoutée',
            'deleted_image'        => 'Image supprimée',
            'created_redirect'     => 'Redirection créée',
            'updated_redirect'     => 'Redirection modifiée',
            'deleted_redirect'     => 'Redirection supprimée',
            'updated_setting'      => 'Paramètre modifié',
            'created_nav_item'     => 'Elément de navigation créé',
            'updated_nav_item'     => 'Elément de navigation modifié',
            'deleted_nav_item'     => 'Elément de navigation supprimé',
            'reordered_nav'        => 'Navigation réordonnée',
            'exported_user_data'   => 'Export données utilisateur',
            'exported_all_users'   => 'Export tous les utilisateurs',
            'imported_users'       => 'Import utilisateurs CSV',
            'exported_module'      => 'Export module ZIP',
            'imported_module'      => 'Import module ZIP',
            'imported_items_csv'   => 'Import items CSV',
            'forced_password_change' => 'Changement de mot de passe forcé',
            'sent_notification'    => 'Notification envoyée',
            'deleted_notification' => 'Notification supprimée',
            'created_emoji'        => 'Emoji ajouté',
            'updated_emoji'        => 'Emoji modifié',
            'deleted_emoji'        => 'Emoji supprimé',
            'imported_emojis'      => 'Pack d\'emojis importé',
            'created_sanction'     => 'Sanction créée',
            'deleted_sanction'     => 'Sanction supprimée',
            'created_theme'        => 'Thème créé',
            'updated_theme'        => 'Thème modifié',
            'deleted_theme'        => 'Thème supprimé',
            'activated_theme'      => 'Thème activé',
            'updated_image'        => 'Image modifiée',
            'deleted_module'       => 'Module supprimé',
            'transferred_module'   => 'Module transféré',
            'treated_report'       => 'Signalement traité',
            'rejected_report'      => 'Signalement rejeté',
        ];
        return $labels[$this->action] ?? $this->action;
    }
}
