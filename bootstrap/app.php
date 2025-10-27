<?php

use App\Models\Tenant;
use App\Services\TenantScopeManager;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(\App\Http\Middleware\IdentifyTenant::class);
        $middleware->alias(['connectedToStripe' => \App\Http\Middleware\connectedToStripeMiddleware::class]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withProviders([
        App\Providers\AuthServiceProvider::class,
    ])
    ->create();
