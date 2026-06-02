<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration — Table des modules.
 * Un module regroupe un ensemble d'items qu'un utilisateur veut mémoriser.
 */
return new class extends Migration
{
    /**
     * Crée la table modules avec toutes ses colonnes.
     */
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();

            // Clé étrangère vers l'utilisateur propriétaire du module
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');

            $table->string('title');                  // Titre du module (ex : "Plantes tropicales")
            $table->text('description')->nullable();  // Description facultative du module
            $table->boolean('is_public')->default(false); // true = visible dans la bibliothèque publique

            $table->timestamps();
        });
    }

    /**
     * Supprime la table modules lors d'un rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
