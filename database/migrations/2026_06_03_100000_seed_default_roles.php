<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Guard: make sure the columns exist before seeding
        if (!Schema::hasColumn('roles', 'color')) {
            return;
        }

        if (DB::table('roles')->count() === 0) {
            DB::table('roles')->insert([
                [
                    'name'          => 'Admin',
                    'color'         => '#dc3545',
                    'power'         => 100,
                    'is_admin_role' => true,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ],
                [
                    'name'          => 'Utilisateur',
                    'color'         => '#6c757d',
                    'power'         => 1,
                    'is_admin_role' => false,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ],
            ]);
        }
    }

    public function down(): void
    {
        //
    }
};
