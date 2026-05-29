<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function (): void {
            Route::middleware('web')->group(base_path('routes/notifications.php'));
            Route::middleware('web')->group(base_path('routes/student-classcards.php'));
            Route::middleware('web')->group(base_path('routes/admin-api.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckUserRole::class,
            'api.ability' => \App\Http\Middleware\EnsureApiTokenCan::class,
            'api.log' => \App\Http\Middleware\LogApiRequest::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'webhooks/stripe',
            'webhooks/hitpay',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
