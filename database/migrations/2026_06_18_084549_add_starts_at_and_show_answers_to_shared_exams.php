<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shared_exams', function (Blueprint $table) {
            $table->timestamp('starts_at')->nullable()->after('expires_at');
            $table->boolean('show_answers')->default(false)->after('starts_at');
        });
    }

    public function down(): void
    {
        Schema::table('shared_exams', function (Blueprint $table) {
            $table->dropColumn(['starts_at', 'show_answers']);
        });
    }
};
