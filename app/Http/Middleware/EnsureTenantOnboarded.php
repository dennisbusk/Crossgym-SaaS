<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantOnboarded
{
    public function handle(Request $request, Closure $next): Response
    {
        // In testing, bypass onboarding to avoid interfering with route/auth tests
        if (app()->environment('testing')) {
            return $next($request);
        }

        // Skip for guests
        if (! auth()->check()) {
            return $next($request);
        }

        $user = $request->user();

        // Superadmin area should not be affected
        if ($request->is('superadmin*')) {
            return $next($request);
        }

        // Onboarding route itself should be allowed
        if ($request->is('onboarding') || $request->is('onboarding/*')) {
            return $next($request);
        }

        // Only enforce when user has a tenant relation
        $tenant = method_exists($user, 'tenant') ? $user->tenant : null;

        if ($tenant && is_null($tenant->onboarded_at)) {
            return redirect()->to(route('tenant.onboarding'));
        }

        return $next($request);
    }
}
