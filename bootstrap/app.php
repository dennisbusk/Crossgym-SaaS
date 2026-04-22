<?php

use App\Models\Tenant;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            'stripe/webhook',
            'webhook/stripe',
        ]);
        $middleware->append(\App\Http\Middleware\IdentifyTenant::class);
        $middleware->append(\App\Http\Middleware\SetThemeMiddleware::class);
        $middleware->alias([
            'connectedToStripe' => \App\Http\Middleware\connectedToStripeMiddleware::class,
            'tenant.onboarded' => \App\Http\Middleware\EnsureTenantOnboarded::class,
            'terms.accepted' => \App\Http\Middleware\EnsureTermsAccepted::class,
        ]);
        // Apply tenant onboarding check globally for authenticated tenant routes
        $middleware->append(\App\Http\Middleware\EnsureTenantOnboarded::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->reportable(function (ServiceUnavailableHttpException $e) {
            Log::error('System hit 503 Service Unavailable (Maintenance Mode)');
        });
    })
    ->withProviders([
        App\Providers\AuthServiceProvider::class,
    ])
    ->create();
