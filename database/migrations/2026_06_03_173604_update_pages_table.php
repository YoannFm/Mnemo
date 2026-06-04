<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('pages', function (Blueprint $table) {
            if (!\Illuminate\Support\Facades\Schema::hasColumn('pages', 'description')) {
                $table->string('description')->nullable()->after('title');
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('pages', 'is_enabled')) {
                $table->boolean('is_enabled')->default(true)->after('content');
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('pages', 'is_restricted')) {
                $table->boolean('is_restricted')->default(false)->after('is_enabled');
            }
        });

        // Migrer is_published -> is_enabled (only if column exists)
        if (\Illuminate\Support\Facades\Schema::hasColumn('pages', 'is_published')) {
            \DB::table('pages')->update(['is_enabled' => \DB::raw('is_published')]);
        }

        if (\Illuminate\Support\Facades\Schema::hasColumn('pages', 'is_published')) {
            Schema::table('pages', function (Blueprint $table) {
                $table->dropColumn('is_published');
            });
        }

        Schema::create('page_role', function (Blueprint $table) {
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->primary(['page_id', 'role_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('page_role');
        Schema::table('pages', function (Blueprint $table) {
            $table->boolean('is_published')->default(false);
        });
        \DB::table('pages')->update(['is_published' => \DB::raw('is_enabled')]);
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['description', 'is_enabled', 'is_restricted']);
        });
    }
};
