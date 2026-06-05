<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('module_rating_deletions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('module_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['rating', 'reply']);
            $table->tinyInteger('rating')->nullable();
            $table->text('content')->nullable(); // comment or reply content
            $table->string('deleted_by')->default('user'); // 'user' or 'admin'
            $table->timestamp('created_at')->useCurrent();
        });
    }
    public function down(): void { Schema::dropIfExists('module_rating_deletions'); }
};
