<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            if (!Schema::hasColumn('roles', 'can_create_module')) {
                $table->boolean('can_create_module')->default(false)->after('is_admin_role');
            }
            if (!Schema::hasColumn('roles', 'can_train_own')) {
                $table->boolean('can_train_own')->default(true)->after('can_create_module');
            }
            if (!Schema::hasColumn('roles', 'can_test_own')) {
                $table->boolean('can_test_own')->default(true)->after('can_train_own');
            }
            if (!Schema::hasColumn('roles', 'can_access_library')) {
                $table->boolean('can_access_library')->default(true)->after('can_test_own');
            }
            if (!Schema::hasColumn('roles', 'can_train_public')) {
                $table->boolean('can_train_public')->default(true)->after('can_access_library');
            }
            if (!Schema::hasColumn('roles', 'can_test_public')) {
                $table->boolean('can_test_public')->default(true)->after('can_train_public');
            }
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn([
                'can_create_module', 'can_train_own', 'can_test_own',
                'can_access_library', 'can_train_public', 'can_test_public',
            ]);
        });
    }
};
