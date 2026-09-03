<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust the Cloudflare Tunnel / any reverse proxy in front of this
        // app so Laravel knows the original request was HTTPS. Without
        // this, $request->secure() is false behind the tunnel, which
        // breaks secure-cookie session persistence and makes logins
        // (admin, manager, customer) appear to silently fail.
        $middleware->trustProxies(at: '*');

        $middleware->append(App\Http\Middleware\ResolveRestaurant::class);

        $middleware->alias([
            'module' => App\Http\Middleware\EnsureModuleEnabled::class,
            'restaurant.admin' => App\Http\Middleware\EnsureRestaurantAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
