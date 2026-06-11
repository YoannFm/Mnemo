<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->boolean('field_name_fr')->default(true)->after('allow_duplication');
            $table->boolean('field_name_alt')->default(true)->after('field_name_fr');
            $table->boolean('field_photo')->default(false)->after('field_name_alt');
            $table->boolean('field_function')->default(false)->after('field_photo');
        });
    }

    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn(['field_name_fr', 'field_name_alt', 'field_photo', 'field_function']);
        });
    }
};
