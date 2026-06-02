<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle de progression Anki.
 * Stocke les statistiques d'apprentissage d'un utilisateur pour un item précis.
 * Un item est considéré maîtrisé quand streak >= 3.
 */
class Progress extends Model
{
    /**
     * Champs assignables en masse.
     */
    protected $fillable = [
        'user_id',
        'item_id',
        'success_count',
        'fail_count',
        'streak',
        'last_seen',
    ];

    /**
     * Cast de la date last_seen en objet Carbon pour faciliter les comparaisons.
     */
    protected $casts = [
        'last_seen' => 'datetime',
    ];

    // ─────────────────────────────────────────────
    // Relations Eloquent
    // ─────────────────────────────────────────────

    /**
     * La progression appartient à un utilisateur.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * La progression concerne un item précis.
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    // ─────────────────────────────────────────────
    // Méthodes métier
    // ─────────────────────────────────────────────

    /**
     * Indique si l'item est considéré comme maîtrisé.
     * Un item est maîtrisé après 3 bonnes réponses consécutives (streak >= 3).
     */
    public function isMastered(): bool
    {
        return $this->streak >= 3;
    }
}
