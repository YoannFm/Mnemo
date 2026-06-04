<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SetTimezone
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $tz = DB::table('settings')->where('key', 'timezone')->value('value');
            if ($tz && in_array($tz, timezone_identifiers_list())) {
                config(['app.timezone' => $tz]);
                date_default_timezone_set($tz);
            }
        } catch (\Exception) {}

        return $next($request);
    }
}
