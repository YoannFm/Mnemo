<?php

namespace App;

class Mnemo
{
    public static function version(): string
    {
        return config('mnemo.version', '1.0.0');
    }

    /**
     * User-Agent envoyé lors des appels HTTP sortants (requis par l'API GitHub).
     */
    public static function userAgent(): string
    {
        return 'Mnemo/' . self::version();
    }
}
