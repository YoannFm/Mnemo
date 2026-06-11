<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null): mixed
    {
        try {
            $value = app('cache')->rememberForever('setting_' . $key, function () use ($key) {
                $setting = static::where('key', $key)->first();
                return $setting ? $setting->value : '__null__';
            });
            return $value !== '__null__' ? $value : $default;
        } catch (\Throwable) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        }
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        try {
            app('cache')->forget('setting_' . $key);
        } catch (\Throwable) {}
    }
}
