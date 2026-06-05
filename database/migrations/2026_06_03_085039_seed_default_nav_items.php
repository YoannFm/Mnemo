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
            ['label' => 'Accueil',      'value' => '/dashboard',    'icon' => 'bi bi-house',      'position' => 1],
            ['label' => 'Mes modules',  'value' => '/modules',      'icon' => 'bi bi-collection', 'position' => 2],
            ['label' => 'Bibliothèque', 'value' => '/bibliotheque', 'icon' => 'bi bi-book',       'position' => 3],
            ['label' => 'Progression',  'value' => '/progression',  'icon' => 'bi bi-graph-up',   'position' => 4],
        ];

        foreach ($defaults as $item) {
            DB::table('nav_items')->insert(array_merge($item, [
                'type'       => 'link',
                'is_active'  => true,
                'new_tab'    => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        DB::table('nav_items')
            ->whereIn('value', ['/dashboard', '/modules', '/bibliotheque', '/progression'])
            ->delete();
    }
};
