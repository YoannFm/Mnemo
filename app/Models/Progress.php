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
        'easiness_factor',
        'interval_days',
        'next_review',
    ];

    /**
     * Cast de la date last_seen en objet Carbon pour faciliter les comparaisons.
     */
    protected $casts = [
        'last_seen'   => 'datetime',
        'next_review' => 'date',
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

    /**
     * Met à jour la progression selon l'algorithme SM-2.
     * @param int $quality Score de 0 à 5 (0-2 = raté, 3-5 = réussi)
     */
    public function applySM2(int $quality): void
    {
        // Mettre à jour l'easiness factor
        $newEF = $this->easiness_factor + (0.1 - (5 - $quality) * (0.08 + (5 - $quality) * 0.02));
        $this->easiness_factor = max(1.3, $newEF); // EF minimum de 1.3

        if ($quality < 3) {
            // Raté : on repart de l'intervalle 1
            $this->interval_days = 1;
        } else {
            // Réussi : calculer le prochain intervalle
            if ($this->success_count === 0) {
                $this->interval_days = 1;
            } elseif ($this->success_count === 1) {
                $this->interval_days = 6;
            } else {
                $this->interval_days = (int) round($this->interval_days * $this->easiness_factor);
            }
        }

        $this->next_review = now()->addDays($this->interval_days)->toDateString();
    }

    /**
     * Retourne true si cet item est dû pour révision (next_review <= aujourd'hui ou jamais révisé).
     */
    public function isDueForReview(): bool
    {
        return $this->next_review === null || $this->next_review->isPast() || $this->next_review->isToday();
    }
}
