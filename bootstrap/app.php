<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        api: __DIR__ . '/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'auth' => App\Http\Middleware\Authenticate::class,
            'logs' => App\Http\Middleware\ApiActivityLog::class,
            'isUserAccessPage' => App\Http\Middleware\IsAccessPage::class,
            'isUser' => App\Http\Middleware\IsUser::class,
            'isAdmin' => App\Http\Middleware\IsAdmin::class,
            'isReseller' => \App\Http\Middleware\IsReseller::class,
            'isSupport' => \App\Http\Middleware\IsSupport::class,

        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

        $exceptions->render(function (Throwable $e, Request $request) {

            if ($request->is('api/*') || $request->expectsJson()) {

                $statusCode = 500;
                $message = 'Something went wrong';

                // Route not found
                if ($e instanceof NotFoundHttpException) {
                    $statusCode = 404;
                    $message = 'Route not found';
                }

                // Other HTTP exceptions
                elseif ($e instanceof HttpExceptionInterface) {
                    $statusCode = $e->getStatusCode();
                    $message = $e->getMessage() ?: 'HTTP Error';
                }

                // Show real error only in local
                if (app()->environment('local')) {
                    $message = $e->getMessage();
                }

                return response()->json([
                    'status' => false,
                    'message' => $message,
                ], $statusCode);
            }

            return null; // non-api requests handled normally
        });
    })->create();
