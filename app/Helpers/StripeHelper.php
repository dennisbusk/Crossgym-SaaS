<?php

declare( strict_types=1 );

namespace App\Helpers;

use App\Models\Tenant;
use Stripe\StripeClient;

class StripeHelper {

    public static function getStripeClient( ?Tenant $tenant = null ): StripeClient {
        $tenant = $tenant ?? ( app()->has('tenant') ? app('tenant') : null );
        $secret = $tenant?->stripe_secret_key ?? ( is_callable(config('stripe.secret')) ? ( config('stripe.secret') )() : config('stripe.secret') );

        return new StripeClient($secret ?? '');
    }
}
