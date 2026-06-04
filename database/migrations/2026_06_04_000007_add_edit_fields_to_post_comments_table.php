<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('post_comments', function (Blueprint $table) {
            $table->boolean('is_deleted')->default(false);
            $table->timestamp('edited_at')->nullable();
        });

        Schema::create('post_comment_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_comment_id')->constrained()->cascadeOnDelete();
            $table->text('content');
            $table->timestamp('created_at')->useCurrent();
        });
    }
    public function down(): void {
        Schema::dropIfExists('post_comment_history');
        Schema::table('post_comments', function (Blueprint $table) {
            $table->dropColumn(['is_deleted', 'edited_at']);
        });
    }
};
