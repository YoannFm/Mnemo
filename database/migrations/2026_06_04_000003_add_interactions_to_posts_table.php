<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('posts', function (Blueprint $table) {
            $table->boolean('allow_reactions')->default(false);
            $table->boolean('allow_comments')->default(false);
        });
    }
    public function down(): void {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['allow_reactions', 'allow_comments']);
        });
    }
};
