<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('nav_items')->count() > 0) {
            return;
        }

        $defaults = [
            ['label' => 'Accueil',      'url' => '/dashboard',    'icon' => 'bi-house',      'position' => 1],
            ['label' => 'Mes modules',  'url' => '/modules',      'icon' => 'bi-collection', 'position' => 2],
            ['label' => 'Bibliothèque', 'url' => '/bibliotheque', 'icon' => 'bi-book',       'position' => 3],
            ['label' => 'Progression',  'url' => '/progression',  'icon' => 'bi-graph-up',   'position' => 4],
        ];

        foreach ($defaults as $item) {
            DB::table('nav_items')->insert(array_merge($item, [
                'is_active'    => true,
                'open_new_tab' => false,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]));
        }
    }

    public function down(): void
    {
        DB::table('nav_items')
            ->whereIn('url', ['/dashboard', '/modules', '/bibliotheque', '/progression'])
            ->delete();
    }
};
