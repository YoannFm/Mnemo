<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckForcePasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        if (
            Auth::check()
            && Auth::user()->force_password_change
            && !$request->routeIs('profile.*')
            && !$request->routeIs('logout')
        ) {
            return redirect()->route('profile.edit')
                ->with('warning', 'Vous devez changer votre mot de passe.');
        }

        return $next($request);
    }
}
