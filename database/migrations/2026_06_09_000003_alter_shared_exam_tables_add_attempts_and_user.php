<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shared_exams', function (Blueprint $table) {
            $table->unsignedInteger('max_attempts')->default(1)->after('expires_at');
        });

        Schema::table('shared_exam_attempts', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('shared_exam_id')->constrained('users')->nullOnDelete();
            $table->timestamp('results_sent_at')->nullable()->after('finished_at');
        });
    }

    public function down(): void
    {
        Schema::table('shared_exam_attempts', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'results_sent_at']);
        });
        Schema::table('shared_exams', function (Blueprint $table) {
            $table->dropColumn('max_attempts');
        });
    }
};
