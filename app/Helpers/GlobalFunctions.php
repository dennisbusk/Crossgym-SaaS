<?php

declare(strict_types=1);

use App\Models\Tenant;

if (! function_exists('tenant')) {
    function tenant(): ?Tenant
    {
        // Prefer an explicitly bound tenant instance
        if (app()->bound('tenant')) {
            return app('tenant');
        }

        // Fallback: resolve from session during testing or simple contexts
        $tenantId = session()->get('tenant_id');
        if ($tenantId) {
            $tenant = Tenant::find($tenantId);
            if ($tenant) {
                // Cache for the remainder of the request to avoid repeated lookups
                app()->instance('tenant', $tenant);

                return $tenant;
            }
        }

        return null;
    }
}

if (! function_exists('connectedToStripe')) {
    function connectedToStripe(): bool
    {
        return ! (! hasRole('superadmin') && ! tenant()?->stripe_connect_onboarded);
    }
}

if (! function_exists('hasRole')) {
    function hasRole($role): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }
        if ($user->role?->slug === $role) {
            return true;
        }
        if ($user->role?->name === $role) {
            return true;
        }

        return false;
    }
}
if (! function_exists('request_cache')) {
    /**
     * Store a value for the duration of the current request.
     * Runtime cache — ikke persistent.
     */
    function request_cache(string $key, callable $callback)
    {
        static $runtime = [];

        if (array_key_exists($key, $runtime)) {
            return $runtime[$key];
        }

        $value = $callback();
        $runtime[$key] = $value;

        return $value;
    }
}
