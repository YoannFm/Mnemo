<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Http\Middleware\HandleRedirects::class);
        $middleware->web(append: [
            \App\Http\Middleware\SetTimezone::class,
            \App\Http\Middleware\CheckInstallation::class,
            \App\Http\Middleware\CheckLicense::class,
            \App\Http\Middleware\CheckMaintenance::class,
            \App\Http\Middleware\CheckForcePasswordChange::class,
        ]);
        $middleware->alias([
            'two-factor' => \App\Http\Middleware\TwoFactorAuth::class,
            'admin' => \App\Http\Middleware\IsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
