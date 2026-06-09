<?php

namespace App\Http\Middleware;

use App\Events\UserActivity;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecordUserActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $userId = auth()->id();
            $cacheKey = "user_activity_fired_{$userId}_" . now()->format('Y-m-d');

            if (!cache()->has($cacheKey)) {
                cache()->put($cacheKey, true, now()->endOfDay());
                UserActivity::dispatch(auth()->user());
            }
        }

        return $next($request);
    }
}
