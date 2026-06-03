<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenance
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Setting::get('maintenance_enabled') == '1') {
            $maintenanceAll = Setting::get('maintenance_all') == '1';

            $bypass = Auth::check() && Auth::user()->is_admin && !$maintenanceAll;

            if (!$bypass) {
                return response(view('maintenance'), 503);
            }
        }

        return $next($request);
    }
}
