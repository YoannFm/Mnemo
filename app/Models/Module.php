<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\SharedExam;

/**
 * Modèle représentant un module de mémorisation.
 * Un module appartient à un utilisateur et contient plusieurs items.
 */
class Module extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Champs que l'on peut assigner en masse (create/fill).
     */
    protected $fillable = [
        'owner_id',
        'title',
        'description',
        'is_public',
        'allow_duplication',
        'field_name_fr',
        'field_name_alt',
        'field_photo',
        'field_function',
        'field_audio',
    ];

    protected $casts = [
        'is_public'         => 'boolean',
        'allow_duplication' => 'boolean',
        'field_name_fr'     => 'boolean',
        'field_name_alt'    => 'boolean',
        'field_photo'       => 'boolean',
        'field_function'    => 'boolean',
        'field_audio'       => 'boolean',
    ];

    // ─────────────────────────────────────────────
    // Relations Eloquent
    // ─────────────────────────────────────────────

    /**
     * Un module appartient à un utilisateur (le propriétaire).
     * Relation : Module → User via la clé étrangère owner_id.
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Un module possède plusieurs items.
     * La suppression d'un module entraîne la suppression de ses items (cascade BDD).
     */
    public function items()
    {
        return $this->hasMany(Item::class);
    }

    /**
     * Un module peut avoir plusieurs scores associés (historique des sessions de test).
     */
    public function scores()
    {
        return $this->hasMany(Score::class);
    }

    public function ratings()
    {
        return $this->hasMany(ModuleRating::class);
    }

    public function sharedExams()
    {
        return $this->hasMany(SharedExam::class);
    }

    public function tags()
    {
        return $this->belongsToMany(\Plugins\Tags\Models\Tag::class, 'module_tag');
    }
}
