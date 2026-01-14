<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Critical: StartSession MUST run first. Only register custom middleware
        // if the class exists to avoid runtime "Target class does not exist" errors.
        $webAppend = [
            \Illuminate\Session\Middleware\StartSession::class,
        ];

        $middleware->web(append: $webAppend);

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'organization.context' => \App\Http\Middleware\EnsureOrganizationContext::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'allow.public' => \App\Http\Middleware\AllowPublicAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Handle TokenMismatchException (Page Expired)
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            return redirect()->route('login')->withErrors([
                'message' => 'Your session has expired. Please login again.'
            ]);
        });
    })->create();