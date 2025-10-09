<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Services\Stripe\StripeTenantClient;
use Stripe\StripeClient;

class StripeHelper
{
    public static function getStripeClient(?StripeTenantClient $tenantClient = null): StripeClient
    {
        $tenantClient = $tenantClient ?? new StripeTenantClient();
        return $tenantClient->client();
    }
}
