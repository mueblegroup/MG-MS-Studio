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
            Route::middleware('web')->group(base_path('routes/student-subscriptions.php'));
            Route::middleware('web')->group(base_path('routes/admin-api.php'));
            Route::middleware('web')->group(base_path('routes/customer.php'));
            Route::middleware('web')->group(base_path('routes/superadmin.php'));
            Route::middleware('web')->group(base_path('routes/subscription-classes.php'));
            Route::middleware('web')->group(base_path('routes/studio-payment-gateways.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(prepend: [
            \App\Http\Middleware\ResolveStudioTenant::class,
            \App\Http\Middleware\ApplyStudioTimezone::class,
            \App\Http\Middleware\ApplyStudioPaymentGatewayConfig::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\ProcessStripeClassRenewalWebhook::class,
            \App\Http\Middleware\RestrictStaffShopPurchases::class,
            \App\Http\Middleware\ReconcileStripeCheckoutReturn::class,
            \App\Http\Middleware\AuditAuthenticatedActions::class,
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\CheckUserRole::class,
            'central' => \App\Http\Middleware\EnsureCentralDomain::class,
            'client.profile.complete' => \App\Http\Middleware\EnsureClientProfileComplete::class,
            'api.ability' => \App\Http\Middleware\EnsureApiTokenCan::class,
            'api.log' => \App\Http\Middleware\LogApiRequest::class,
            'studio' => \App\Http\Middleware\ResolveStudioTenant::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'webhooks/stripe',
            'webhooks/hitpay',
            'webhooks/platform-stripe',
            'auth/apple/callback',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
