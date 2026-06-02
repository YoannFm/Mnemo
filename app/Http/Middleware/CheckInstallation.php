<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;

class CheckInstallation
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            if (!$this->isInstalled() && !$request->is('install*')) {
                return redirect()->route('install.index');
            }

            if ($this->isInstalled() && $request->is('install*')) {
                return redirect('/');
            }
        } catch (\Exception) {
            if (!$request->is('install*')) {
                return redirect()->route('install.index');
            }
        }

        return $next($request);
    }

    private function isInstalled(): bool
    {
        try {
            if (!DB::connection()->getDatabaseName()) {
                return false;
            }

            return DB::table('installation')
                ->where('completed', true)
                ->exists();
        } catch (\Exception) {
            return false;
        }
    }
}
