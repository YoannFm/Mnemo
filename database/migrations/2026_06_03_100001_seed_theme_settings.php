<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $defaults = [
            'theme_body_bg'    => '#111113',
            'theme_content_bg' => '#2E2E34',
            'theme_card_bg'    => '#212227',
            'theme_accent'     => '#EFB702',
            'theme_header_bg'  => '#1a1b1f',
            'theme_text_color' => '#e2e8f0',
        ];

        foreach ($defaults as $key => $value) {
            $exists = DB::table('settings')->where('key', $key)->exists();
            if (! $exists) {
                DB::table('settings')->insert([
                    'key'        => $key,
                    'value'      => $value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'theme_body_bg', 'theme_content_bg', 'theme_card_bg',
            'theme_accent', 'theme_header_bg', 'theme_text_color',
        ])->delete();
    }
};
