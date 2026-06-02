<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration - Table des scores (mode Test).
 * Historique de chaque session de test terminée par un utilisateur.
 */
return new class extends Migration
{
    /**
     * Crée la table scores avec toutes ses colonnes.
     */
    public function up(): void
    {
        Schema::create('scores', function (Blueprint $table) {
            $table->id();

            // Clé étrangère vers l'utilisateur qui a passé le test
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Clé étrangère vers le module testé
            $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');

            $table->unsignedInteger('score');  // Nombre de bonnes réponses obtenues
            $table->unsignedInteger('total');  // Nombre total de questions posées

            $table->timestamps();
        });
    }

    /**
     * Supprime la table scores lors d'un rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('scores');
    }
};
