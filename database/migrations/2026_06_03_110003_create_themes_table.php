<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('themes')) {
            Schema::create('themes', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->boolean('is_active')->default(false);
                $table->string('body_bg', 20)->default('#111113');
                $table->string('content_bg', 20)->default('#2E2E34');
                $table->string('card_bg', 20)->default('#212227');
                $table->string('accent_color', 20)->default('#EFB702');
                $table->string('header_bg', 20)->default('#1a1b1f');
                $table->string('text_color', 20)->default('#e2e8f0');
                $table->timestamps();
            });

            // Seed par defaut
            DB::table('themes')->insert([
                'name'        => 'Mnemo Default',
                'slug'        => 'mnemo-default',
                'is_active'   => true,
                'body_bg'     => '#111113',
                'content_bg'  => '#2E2E34',
                'card_bg'     => '#212227',
                'accent_color'=> '#EFB702',
                'header_bg'   => '#1a1b1f',
                'text_color'  => '#e2e8f0',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('themes');
    }
};
