<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Services\TenantScopeManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        // Skip in console/testing to avoid blocking migrations and tests
        if (app()->runningInConsole() || app()->environment('testing')) {
            return $next($request);
        }

        // Exempt central and system routes from tenant enforcement
        if (
            $request->is('stripe/webhook') ||
            $request->is('webhook/stripe') ||
            $request->is('superadmin') ||
            $request->is('superadmin/*') ||
            $request->is('email/track-open/*')
        ) {
            return $next($request);
        }

        $host = $request->getHost();
        $tenant = Tenant::where('domain', $host)->first();

        if (! $tenant) {
            abort(404, 'Tenant not found.');
        }
        config()->set('session.domain', $tenant->domain);
        session(['tenant_id' => $tenant->id]);
        app()->instance('tenant', $tenant);
        $scopeManager = app(TenantScopeManager::class);
        $scopeManager->applyScopes($tenant);

        return $next($request);
    }
}
