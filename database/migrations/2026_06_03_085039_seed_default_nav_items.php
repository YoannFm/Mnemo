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

        $hasValue = \Illuminate\Support\Facades\Schema::hasColumn('nav_items', 'value');
        $hasType  = \Illuminate\Support\Facades\Schema::hasColumn('nav_items', 'type');
        $hasNewTab = \Illuminate\Support\Facades\Schema::hasColumn('nav_items', 'new_tab');

        $defaults = [
            ['label' => 'Accueil',      'url_or_value' => '/dashboard',    'icon' => 'bi bi-house',      'position' => 1],
            ['label' => 'Mes modules',  'url_or_value' => '/modules',      'icon' => 'bi bi-collection', 'position' => 2],
            ['label' => 'Bibliothèque', 'url_or_value' => '/bibliotheque', 'icon' => 'bi bi-book',       'position' => 3],
            ['label' => 'Progression',  'url_or_value' => '/progression',  'icon' => 'bi bi-graph-up',   'position' => 4],
        ];

        foreach ($defaults as $item) {
            $row = [
                'label'      => $item['label'],
                'icon'       => $item['icon'],
                'position'   => $item['position'],
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if ($hasValue) {
                $row['value'] = $item['url_or_value'];
            } else {
                $row['url'] = $item['url_or_value'];
            }

            if ($hasType) {
                $row['type'] = 'link';
            }

            if ($hasNewTab) {
                $row['new_tab'] = false;
            } else {
                $row['open_new_tab'] = false;
            }

            DB::table('nav_items')->insert($row);
        }
    }

    public function down(): void
    {
        DB::table('nav_items')
            ->whereIn('value', ['/dashboard', '/modules', '/bibliotheque', '/progression'])
            ->delete();
    }
};
