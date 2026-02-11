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

        // Add site connection middleware BEFORE other web middleware
        // so DB is configured before auth checks
        $middleware->web(append: [\App\Http\Middleware\SetSiteConnection::class]);

        // Configure Authenticate middleware redirect
        $middleware->redirectGuestsTo('/login');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
