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
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->throttleApi();
        
        $middleware->alias([
            'throttle.strict' => \Illuminate\Routing\Middleware\ThrottleRequests::class . ':10,1',
            'throttle.auth' => \Illuminate\Routing\Middleware\ThrottleRequests::class . ':100,1',
            'throttle.uploads' => \Illuminate\Routing\Middleware\ThrottleRequests::class . ':5,1',
            'ability' => \App\Http\Middleware\CheckTokenAbility::class, // <- This line is CRITICAL
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
