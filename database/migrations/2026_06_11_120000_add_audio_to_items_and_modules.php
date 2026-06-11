<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->string('audio_path')->nullable()->after('photo_path');
        });
        Schema::table('modules', function (Blueprint $table) {
            $table->boolean('field_audio')->default(false)->after('field_function');
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('audio_path');
        });
        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn('field_audio');
        });
    }
};
