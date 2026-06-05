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
        Schema::table('post_reactions', function (Blueprint $table) {
            $table->string('emoji', 100)->change();
        });
    }

    public function down(): void
    {
        Schema::table('post_reactions', function (Blueprint $table) {
            $table->string('emoji', 10)->change();
        });
    }
};
