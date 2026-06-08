<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modèle représentant un item à mémoriser.
 * Contient : photo, nom français, nom anglais et fonction/description.
 */
class Item extends Model
{
    use HasFactory;

    /**
     * Champs que l'on peut assigner en masse.
     */
    protected $fillable = [
        'module_id',
        'name_fr',
        'name_en',
        'function_text',
        'photo_path',
    ];

    // ─────────────────────────────────────────────
    // Relations Eloquent
    // ─────────────────────────────────────────────

    /**
     * Un item appartient à un module.
     */
    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * Un item peut avoir plusieurs entrées de progression (une par utilisateur).
     */
    public function progresses()
    {
        return $this->hasMany(Progress::class);
    }

    // ─────────────────────────────────────────────
    // Accesseurs
    // ─────────────────────────────────────────────

    /**
     * Retourne l'URL publique de la photo ou une image placeholder si aucune n'est définie.
     * Utilise le lien symbolique créé par php artisan storage:link.
     */
    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo_path) {
            return asset('storage/' . $this->photo_path);
        }

        return asset('images/no-photo.svg');
    }

    public function getDisplayFunctionTextAttribute(): string
    {
        return $this->function_text ?: 'Aucune description renseignée.';
    }
}
