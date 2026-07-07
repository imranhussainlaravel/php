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
        // CORS middleware must run first on every request (including OPTIONS preflight)
        $middleware->prepend(\App\Http\Middleware\CorsMiddleware::class);

        $middleware->alias([
            'check.origin'     => \App\Http\Middleware\CheckOrigin::class,
            'verify.turnstile' => \App\Http\Middleware\VerifyTurnstile::class,
            'no.cache'         => \App\Http\Middleware\NoCacheHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
