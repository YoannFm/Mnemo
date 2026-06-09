<?php

namespace App;

class Mnemo
{
    public static function version(): string
    {
        return config('mnemo.version', '1.0.0');
    }
}
