<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    public $timestamps = false;
    protected $fillable = ['user_id', 'action', 'target_type', 'target_id', 'data', 'level', 'old_value', 'new_value', 'created_at'];

    protected function casts(): array
    {
        return ['data' => 'array'];
    }

    public function getCreatedAtAttribute($value): ?\Carbon\Carbon
    {
        if (!$value) return null;
        return \Carbon\Carbon::parse($value, 'UTC')->setTimezone(config('app.timezone', 'Europe/Paris'));
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

            // Auth
            'login'               => 'Connexion',
            'logout'              => 'Déconnexion',
            'registered'          => 'Inscription',
            'password_changed'    => 'Mot de passe modifié',

            // Modules
            'created_module'              => 'Module créé',
            'updated_module'              => 'Module modifié',
            'permanently_deleted_module'  => 'Module définitivement supprimé',
            'restored_module'             => 'Module restauré',
            'duplicated_module'           => 'Module dupliqué',
            'rated_module'                => 'Module évalué',
            'deleted_own_rating'          => 'Évaluation supprimée',
            'replied_to_rating'           => 'Réponse à un avis',
            'deleted_rating_reply'        => 'Réponse à un avis supprimée',

            // Tests & exams
            'completed_test'      => 'Test terminé',
            'completed_exam'      => 'Examen terminé',

            // Items
            'created_item'        => 'Item créé',
            'updated_item'        => 'Item modifié',
            'deleted_item'        => 'Item supprimé',
            'imported_items_csv'  => 'Items importés (CSV)',

            // Profile
            'updated_profile'     => 'Profil modifié',
            'deleted_account'     => 'Compte supprimé',

            // Shared exams
            'created_shared_exam' => 'Examen partagé créé',
            'deleted_shared_exam' => 'Examen partagé supprimé',
            'added_attempts'      => 'Tentatives ajoutées',
            'reset_attempt'       => 'Tentative réinitialisée',
            'sent_results'        => 'Résultats envoyés',
            'sent_all_results'    => 'Tous les résultats envoyés',

            // Admin users
            'admin_created_user'           => 'Utilisateur créé (admin)',
            'admin_updated_user'           => 'Utilisateur modifié (admin)',
            'admin_deleted_user'           => 'Utilisateur supprimé (admin)',
            'admin_forced_password_change' => 'Changement de mot de passe forcé',
            'admin_imported_users'         => 'Utilisateurs importés',

            // Admin themes
            'created_theme'   => 'Thème créé',
            'updated_theme'   => 'Thème modifié',
            'deleted_theme'   => 'Thème supprimé',
            'activated_theme' => 'Thème activé',

            // Admin emojis
            'created_emoji'       => 'Emoji créé',
            'imported_emoji_pack' => 'Pack d\'emojis importé',
            'deleted_emoji'       => 'Emoji supprimé',

            // Admin navbar
            'created_nav_item'  => 'Élément de navigation créé',
            'updated_nav_item'  => 'Élément de navigation modifié',
            'deleted_nav_item'  => 'Élément de navigation supprimé',
            'updated_nav_order' => 'Ordre de navigation mis à jour',

            // Admin notifications
            'sent_system_notification'    => 'Notification système envoyée',
            'deleted_system_notification' => 'Notification système supprimée',

            // Admin logs
            'cleared_all_logs' => 'Logs effacés',
            'purged_logs'      => 'Logs purgés',

            // Admin sanctions
            'unmuted_user' => 'Utilisateur démute',

            // Admin plugins
            'reloaded_plugins'  => 'Plugins rechargés',
            'enabled_plugin'    => 'Plugin activé',
            'disabled_plugin'   => 'Plugin désactivé',
            'installed_plugin'  => 'Plugin installé',
            'updated_plugin'    => 'Plugin mis à jour',
            'deleted_plugin'    => 'Plugin supprimé',

            // Admin roles
            'updated_roles_order' => 'Ordre des rôles mis à jour',

            // Admin mail settings
            'sent_test_mail' => 'Mail de test envoyé',

            // Anki
            'started_anki'        => 'Session Anki démarrée',
            'completed_anki'      => 'Session Anki terminée',
            'started_anki_review' => 'Révision Anki démarrée',

            // Test & Exam start
            'started_test' => 'Test démarré',
            'started_exam' => 'Examen démarré',

            // Groups
            'created_group'        => 'Groupe créé',
            'deleted_group'        => 'Groupe supprimé',
            'added_group_member'   => 'Membre ajouté au groupe',
            'removed_group_member' => 'Membre retiré du groupe',

            // Module actions
            'reset_progress'  => 'Progression réinitialisée',
            'reported_module' => 'Module signalé',
            'exported_module' => 'Module exporté',
            'imported_module' => 'Module importé',

            // Auth / Security
            '2fa_enabled'    => '2FA activée',
            '2fa_disabled'   => '2FA désactivée',
            'password_reset' => 'Mot de passe réinitialisé',
            'email_verified' => 'Email vérifié',

            // Updates
            'downloaded_update' => 'Mise à jour téléchargée',
            'installed_update'  => 'Mise à jour installée',
            'backup_database'   => 'Sauvegarde base de données',
            'backup_files'      => 'Sauvegarde fichiers',

            // Logs export
            'exported_logs' => 'Logs exportés',
        ];
        return $labels[$this->action] ?? $this->action;
    }
}
