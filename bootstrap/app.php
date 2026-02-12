<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register Spatie Permission Middleware Aliases
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'site' => \App\Http\Middleware\SetSiteConnection::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        ]);

        // Add site connection middleware to web group
        // Must use append (not prepend) because it needs session access from StartSession
        $middleware->web(append: [\App\Http\Middleware\SetSiteConnection::class]);

        // Ensure SetSiteConnection runs BEFORE auth middleware
        // Laravel sorts combined middleware (web group + route middleware) by priority.
        // Without this, auth (priority 5) runs before SetSiteConnection (no priority),
        // causing auth to check the wrong database before the site connection is configured.
        $middleware->priority([
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\SetSiteConnection::class,
            \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \Illuminate\Routing\Middleware\ThrottleRequestsWithRedis::class,
            \Illuminate\Contracts\Session\Middleware\AuthenticatesSessions::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Auth\Middleware\Authorize::class,
        ]);

        // Configure Authenticate middleware redirect
        $middleware->redirectGuestsTo('/login');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
