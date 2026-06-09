<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shared_exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shared_exam_id')->constrained('shared_exams')->cascadeOnDelete();
            $table->string('guest_name');
            $table->json('answers');
            $table->integer('score')->default(0);
            $table->integer('total')->default(0);
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shared_exam_attempts');
    }
};
