<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shared_exams', function (Blueprint $table) {
            $table->string('webhook_url')->nullable()->after('max_attempts');
        });
    }

    public function down(): void
    {
        Schema::table('shared_exams', function (Blueprint $table) {
            $table->dropColumn('webhook_url');
        });
    }
};
