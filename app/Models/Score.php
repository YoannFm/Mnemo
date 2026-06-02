<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle Score - historique des sessions de test.
 * Chaque ligne représente une session de test terminée par un utilisateur sur un module.
 */
class Score extends Model
{
    /**
     * Champs assignables en masse.
     */
    protected $fillable = [
        'user_id',
        'module_id',
        'score',
        'total',
    ];

    // ─────────────────────────────────────────────
    // Relations Eloquent
    // ─────────────────────────────────────────────

    /**
     * Le score appartient à un utilisateur.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Le score est associé à un module.
     */
    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    // ─────────────────────────────────────────────
    // Accesseurs
    // ─────────────────────────────────────────────

    /**
     * Calcule et retourne le pourcentage de réussite arrondi à l'entier.
     * Ex : score=8, total=10 → 80
     */
    public function getPercentageAttribute(): int
    {
        if ($this->total === 0) {
            return 0;
        }

        return (int) round(($this->score / $this->total) * 100);
    }
}
