<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Supprime la clé du site (ancienne clé de licence), devenue inutile
     * depuis le passage du projet en open source.
     */
    public function up(): void
    {
        DB::table('settings')->where('key', 'site_key')->delete();
        Cache::forget('setting_site_key');
    }

    public function down(): void
    {
        // Suppression définitive : la clé n'est pas restaurée.
    }
};
