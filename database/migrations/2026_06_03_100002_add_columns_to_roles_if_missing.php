<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('roles', 'name')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->string('name');
            });
        }
        if (!Schema::hasColumn('roles', 'color')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->string('color', 7)->default('#2196f3');
            });
        }
        if (!Schema::hasColumn('roles', 'power')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->integer('power')->default(0);
            });
        }
        if (!Schema::hasColumn('roles', 'is_admin_role')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->boolean('is_admin_role')->default(false);
            });
        }
    }

    public function down(): void {}
};
