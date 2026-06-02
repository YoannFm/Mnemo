<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration — Table des items.
 * Un item représente un élément à mémoriser (photo + nom FR + nom EN + fonction).
 */
return new class extends Migration
{
    /**
     * Crée la table items avec toutes ses colonnes.
     */
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();

            // Clé étrangère vers le module auquel appartient cet item
            $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');

            $table->string('name_fr');              // Nom de l'item en français
            $table->string('name_en');              // Nom de l'item en anglais
            $table->text('function_text');          // Description / fonction de l'item
            $table->string('photo_path')->nullable(); // Chemin vers l'image stockée dans storage/public

            $table->timestamps();
        });
    }

    /**
     * Supprime la table items lors d'un rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
