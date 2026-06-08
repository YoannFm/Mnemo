<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('module_rating_reply_reports', function (Blueprint $table) {
            $indexes = Schema::getIndexes('module_rating_reply_reports');
            $exists  = collect($indexes)->contains(fn($i) => $i['name'] === 'mrr_reports_unique');

            if (! $exists) {
                $table->unique(['module_rating_reply_id', 'user_id'], 'mrr_reports_unique');
            }
        });
    }

    public function down(): void {
        Schema::table('module_rating_reply_reports', function (Blueprint $table) {
            $table->dropUnique('mrr_reports_unique');
        });
    }
};
