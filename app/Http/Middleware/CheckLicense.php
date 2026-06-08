<?php

namespace App\Http\Middleware;

use App\Services\LicenseService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckLicense
{
    public function handle(Request $request, Closure $next): Response
    {
        // Passer les routes de licence et d'installation
        if ($request->routeIs('license.*') || $request->routeIs('install.*')) {
            return $next($request);
        }

        if (!LicenseService::isValid()) {
            $data = LicenseService::getData();

            if ($request->expectsJson()) {
                return response()->json(['error' => 'License invalid.', 'reason' => $data['reason'] ?? null], 403);
            }

            return response()->view('errors.license', ['data' => $data], 403);
        }

        return $next($request);
    }
}
