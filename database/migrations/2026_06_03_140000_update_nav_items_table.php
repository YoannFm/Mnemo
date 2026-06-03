<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nav_items', function (Blueprint $table) {
            if (!Schema::hasColumn('nav_items', 'type')) {
                $table->string('type')->default('link')->after('label');
            }
            if (!Schema::hasColumn('nav_items', 'value')) {
                $table->text('value')->nullable()->after('type');
            }
            if (!Schema::hasColumn('nav_items', 'parent_id')) {
                $table->unsignedBigInteger('parent_id')->nullable()->after('value');
                $table->foreign('parent_id')->references('id')->on('nav_items')->nullOnDelete();
            }
            if (!Schema::hasColumn('nav_items', 'new_tab')) {
                $table->boolean('new_tab')->default(false)->after('icon');
            }
        });

        // Migrer les items existants : type=link, value=url
        if (Schema::hasColumn('nav_items', 'url')) {
            DB::statement("UPDATE nav_items SET type='link', value=url WHERE type IS NULL OR type=''");
        }
    }

    public function down(): void
    {
        Schema::table('nav_items', function (Blueprint $table) {
            if (Schema::hasColumn('nav_items', 'parent_id')) {
                $table->dropForeign(['parent_id']);
                $table->dropColumn('parent_id');
            }
            foreach (['type', 'value', 'new_tab'] as $col) {
                if (Schema::hasColumn('nav_items', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
