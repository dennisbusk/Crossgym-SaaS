<?php

use App\Models\Tenant;

if (! function_exists('tenant')) {
    function tenant(): ?Tenant
    {
        return app()->bound('tenant') ? app('tenant') : null;
    }
}
if (! function_exists('connectedToStripe')) {
    function connectedToStripe(): bool{
    return !(!hasRole('superadmin') && !tenant()?->stripe_connect_onboarded);
    }
}
if(!function_exists('hasRole')){
    function hasRole($role){
        if(auth()->user()->role?->slug === $role){
            return true;
        }
        if(auth()->user()->role?->name === $role){
            return true;
        }
        return false;
    }
}
