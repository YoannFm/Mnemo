<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $map = [
            'Accueil'      => '/dashboard',
            'Mes modules'  => '/modules',
            'Bibliothèque' => '/bibliotheque',
            'Progression'  => '/progression',
        ];

        foreach ($map as $label => $url) {
            \DB::table('nav_items')
                ->where('label', $label)
                ->whereNull('value')
                ->update(['value' => $url]);
        }
    }

    public function down(): void
    {
        //
    }
};
