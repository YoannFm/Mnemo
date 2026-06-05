<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('nav_items')->whereIn('label', ['Accueil', 'Mes modules', 'Biblio publique', 'Progression'])->update(['is_protected' => true]);
    }

    public function down(): void
    {
        DB::table('nav_items')->whereIn('label', ['Accueil', 'Mes modules', 'Biblio publique', 'Progression'])->update(['is_protected' => false]);
    }
};
