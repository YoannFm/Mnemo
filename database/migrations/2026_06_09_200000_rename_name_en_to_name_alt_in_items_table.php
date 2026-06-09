<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', fn (Blueprint $t) => $t->renameColumn('name_en', 'name_alt'));
    }

    public function down(): void
    {
        Schema::table('items', fn (Blueprint $t) => $t->renameColumn('name_alt', 'name_en'));
    }
};
