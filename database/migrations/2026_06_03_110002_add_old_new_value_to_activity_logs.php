<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('activity_logs', 'level')) {
                $table->string('level', 20)->default('info')->after('data');
            }
            if (!Schema::hasColumn('activity_logs', 'old_value')) {
                $table->text('old_value')->nullable()->after('level');
            }
            if (!Schema::hasColumn('activity_logs', 'new_value')) {
                $table->text('new_value')->nullable()->after('old_value');
            }
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropColumn(['level', 'old_value', 'new_value']);
        });
    }
};
