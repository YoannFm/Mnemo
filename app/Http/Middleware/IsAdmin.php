<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check() || ! Auth::user()->isAdmin()) {
            abort(403, 'Accès réservé aux administrateurs.');
        }

        if (\App\Models\Setting::get('admin_2fa_required', '0') === '1') {
            $user = Auth::user();
            if (! $user->hasTwoFactorAuth()) {
                return redirect()->route('profile.2fa.show')
                    ->with('warning', 'L\'authentification à deux facteurs est obligatoire pour accéder au panel d\'administration.');
            }
            if (! $request->session()->get('two_factor_verified')) {
                return redirect()->route('two-factor.show');
            }
        }

        return $next($request);
    }
}
