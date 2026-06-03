<?php

if (! function_exists('setting')) {
    function setting(string $key, $default = null): mixed
    {
        try {
            return \App\Models\Setting::get($key, $default);
        } catch (\Throwable $e) {
            return $default;
        }
    }
}
