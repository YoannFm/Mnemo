<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('themes', function (Blueprint $table) {
            $table->string('light_body_bg', 20)->default('#f0f2f5');
            $table->string('light_content_bg', 20)->default('#e9ecef');
            $table->string('light_card_bg', 20)->default('#ffffff');
            $table->string('light_header_bg', 20)->default('#ffffff');
            $table->string('light_text_color', 20)->default('#212529');
        });
    }
    public function down(): void {
        Schema::table('themes', function (Blueprint $table) {
            $table->dropColumn(['light_body_bg','light_content_bg','light_card_bg','light_header_bg','light_text_color']);
        });
    }
};
