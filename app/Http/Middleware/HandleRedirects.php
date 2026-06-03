<?php
namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleRedirects
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->getStatusCode() === 404) {
            $path = '/' . ltrim($request->getPathInfo(), '/');
            $redirect = Redirect::where('source', $path)
                ->where('is_enabled', true)
                ->first();

            if ($redirect) {
                return redirect($redirect->target, $redirect->type);
            }
        }

        return $response;
    }
}
