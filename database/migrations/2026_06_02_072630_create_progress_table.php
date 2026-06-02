<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration - Table de progression (mode Anki).
 * Enregistre pour chaque utilisateur le nombre de succès/échecs par item.
 */
return new class extends Migration
{
    /**
     * Crée la table progress avec toutes ses colonnes.
     */
    public function up(): void
    {
        Schema::create('progress', function (Blueprint $table) {
            $table->id();

            // Clé étrangère vers l'utilisateur qui s'entraîne
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Clé étrangère vers l'item concerné
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');

            $table->unsignedInteger('success_count')->default(0); // Nombre de bonnes réponses cumulées
            $table->unsignedInteger('fail_count')->default(0);    // Nombre de mauvaises réponses cumulées
            $table->unsignedInteger('streak')->default(0);        // Série de bonnes réponses consécutives (≥3 = maîtrisé)
            $table->timestamp('last_seen')->nullable();           // Date de la dernière fois que l'item a été présenté

            // Un utilisateur ne peut avoir qu'une entrée par item
            $table->unique(['user_id', 'item_id']);

            $table->timestamps();
        });
    }

    /**
     * Supprime la table progress lors d'un rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('progress');
    }
};
