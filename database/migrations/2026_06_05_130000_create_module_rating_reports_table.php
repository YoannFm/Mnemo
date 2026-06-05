<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_rating_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_rating_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reason');
            $table->text('note')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
            $table->unique(['module_rating_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_rating_reports');
    }
};
