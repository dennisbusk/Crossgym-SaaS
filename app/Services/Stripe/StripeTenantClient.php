<?php

declare(strict_types=1);

namespace App\Services\Stripe;

use App\Models\Tenant;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class StripeTenantClient
{
    public function __construct(
        protected ?Tenant $tenant = null,
    ) {
        $this->tenant = $tenant ?? tenant(); // assumes a helper/middleware sets current tenant
    }

    public function client(): StripeClient
    {
        $secret = $this->secretKey();

        return new StripeClient($secret);
    }

    public function options(): array
    {
        $account = $this->tenant?->stripe_connect_account_id;
        return $account ? ['stripe_account' => $account] : [];
    }

    public function publicKey(): string
    {
        return (string)($this->tenant?->stripe_public_key ?? config('services.stripe.key'));
    }

    public function secretKey(): string
    {
        return (string)($this->tenant?->stripe_secret_key ?? config('services.stripe.secret'));
    }

    public function webhookSecret(): ?string
    {
        return $this->tenant?->stripe_webhook_secret ?? config('services.stripe.webhook_secret');
    }

    public function isConnectedAccount(): bool
    {
        return filled($this->tenant?->stripe_connect_account_id);
    }
}
