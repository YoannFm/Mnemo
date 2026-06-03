<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('roles', 'can_create_module')) {
            return;
        }

        // Admin - tout a true
        DB::table('roles')->where('is_admin_role', true)->update([
            'can_create_module'  => true,
            'can_train_own'      => true,
            'can_test_own'       => true,
            'can_access_library' => true,
            'can_train_public'   => true,
            'can_test_public'    => true,
        ]);

        // Utilisateur - tout sauf can_create_module
        DB::table('roles')->where('is_admin_role', false)->update([
            'can_create_module'  => false,
            'can_train_own'      => true,
            'can_test_own'       => true,
            'can_access_library' => true,
            'can_train_public'   => true,
            'can_test_public'    => true,
        ]);
    }

    public function down(): void {}
};
