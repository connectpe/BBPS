<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        api: __DIR__.'/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'auth' => App\Http\Middleware\Authenticate::class,
            'logs' => App\Http\Middleware\ApiActivityLog::class,
            'isUserAccessPage'=>App\Http\Middleware\IsAccessPage::class,
            'isUser'=>App\Http\Middleware\IsUser::class,
            'isAdmin'=>App\Http\Middleware\IsAdmin::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
