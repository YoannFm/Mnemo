<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('progress', function (Blueprint $table) {
            $table->float('easiness_factor')->default(2.5)->after('streak'); // Facteur de facilité SM-2
            $table->integer('interval_days')->default(1)->after('easiness_factor'); // Intervalle en jours
            $table->date('next_review')->nullable()->after('interval_days'); // Prochaine révision planifiée
        });
    }
    public function down(): void {
        Schema::table('progress', function (Blueprint $table) {
            $table->dropColumn(['easiness_factor', 'interval_days', 'next_review']);
        });
    }
};
