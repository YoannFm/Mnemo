<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->hasTwoFactorAuth() && ! $request->session()->get('two_factor_verified')) {
            return redirect()->route('two-factor.show');
        }

        return $next($request);
    }
}
