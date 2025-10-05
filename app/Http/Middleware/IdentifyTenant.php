<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
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

        $host = $request->getHost();
        $tenant = Tenant::where('domain', $host)->first();

        if (! $tenant) {
            abort(404, 'Tenant not found.');
        }

        session(['tenant_id' => $tenant->id]);
        app()->instance('tenant', $tenant);

        return $next($request);
    }
}
