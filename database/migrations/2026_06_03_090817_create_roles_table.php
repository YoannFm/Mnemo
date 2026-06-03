<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color', 7)->default('#2196f3');
            $table->integer('power')->default(0);
            $table->boolean('is_admin_role')->default(false);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('roles'); }
};
