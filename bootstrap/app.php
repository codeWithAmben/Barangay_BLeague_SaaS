<?php

use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\EnsureTenant;
use App\Http\Middleware\ResolveTenantFromAuth;
use App\Http\Middleware\ResolveTenantFromRoute;
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
        $middleware->web(append: [
            ResolveTenantFromAuth::class,
        ]);

        $middleware->alias([
            'tenant' => EnsureTenant::class,
            'tenant.route' => ResolveTenantFromRoute::class,
            'role' => EnsureRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
