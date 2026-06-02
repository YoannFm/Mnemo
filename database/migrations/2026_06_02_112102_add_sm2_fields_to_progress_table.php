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
        Schema::table('progress', function (Blueprint $table) {
            $table->double('easiness_factor')->default(2.5)->after('streak');
            $table->integer('interval_days')->default(1)->after('easiness_factor');
            $table->date('next_review')->nullable()->after('interval_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('progress', function (Blueprint $table) {
            $table->dropColumn(['easiness_factor', 'interval_days', 'next_review']);
        });
    }
};
