<?php

declare(strict_types=1);

namespace App\Services\Stripe;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Stripe\StripeClient;

class StripeConnectService
{
    public function __construct(
        protected ?StripeTenantClient $tenantClient = null,
    ) {
        $this->tenantClient = $tenantClient ?? new StripeTenantClient();
    }

    /**
     * Create or refresh a Hosted Onboarding Account Link for the given tenant.
     * Returns the URL to redirect the user to.
     */
    public function createAccountLink(Tenant $tenant): string
    {
        $client = $this->platformClient();

        // Create Express account if not present
        if (empty($tenant->stripe_connect_account_id)) {
            $account = $client->accounts->create([
                'type' => 'express',
                'email' => $tenant->stripe_connect_email ?: (string)optional($tenant->users()->first())->email,
                'capabilities' => [
                    'card_payments' => ['requested' => true],
                    'transfers' => ['requested' => true],
                ],
                'business_type' => 'company',
                'metadata' => [
                    'tenant_id' => $tenant->id,
                    'tenant_domain' => $tenant->domain,
                ],
            ]);

            $tenant->forceFill([
                'stripe_connect_account_id' => $account->id,
                'stripe_connect_email' => $account->email ?? null,
            ])->save();
        }

        // Create an account link (hosted onboarding)
        $link = $client->accountLinks->create([
            'account' => (string)$tenant->stripe_connect_account_id,
            'refresh_url' => route('stripe.connect.refresh'),
            'return_url' => route('stripe.connect.return'),
            'type' => 'account_onboarding',
        ]);

        return (string)$link->url;
    }

    /**
     * Handle OAuth callback (Standard Connect). Kept for compatibility if using OAuth.
     * Stores tokens and account id on the tenant.
     */
    public function handleOAuthCallback(Request $request): void
    {
        $code = (string)$request->string('code');
        if (!$code) {
            abort(400, __('Missing code'));
        }

        // Exchange code for tokens
        $resp = \Http::asForm()->post('https://connect.stripe.com/oauth/token', [
            'client_secret' => config('services.stripe.secret'),
            'code' => $code,
            'grant_type' => 'authorization_code',
        ]);

        if (!$resp->ok()) {
            abort(400, __('Stripe connection failed.'));
        }

        $data = $resp->json();
        /** @var Tenant $tenant */
        $tenant = tenant();
        $tenant->forceFill([
            'stripe_connect_account_id' => $data['stripe_user_id'] ?? $tenant->stripe_connect_account_id,
            'stripe_connect_refresh_token' => $data['refresh_token'] ?? $tenant->stripe_connect_refresh_token,
            'stripe_connect_access_token' => $data['access_token'] ?? $tenant->stripe_connect_access_token,
            'stripe_connect_email' => $data['stripe_user_email'] ?? $tenant->stripe_connect_email,
        ])->save();

        // Update status from Stripe
        if (!empty($tenant->stripe_connect_account_id)) {
            $this->updateTenantAccountStatus($tenant->stripe_connect_account_id);
        }
    }

    /**
     * Pull account status from Stripe and update the tenant flags.
     */
    public function updateTenantAccountStatus(string $accountId): void
    {
        $client = $this->platformClient();
        $acct = $client->accounts->retrieve($accountId);

        /** @var Tenant|null $tenant */
        $tenant = Tenant::where('stripe_connect_account_id', $acct->id)->first();
        if (!$tenant) return;

        $tenant->forceFill([
            'stripe_connect_charges_enabled' => (bool)($acct->charges_enabled ?? false),
            'stripe_connect_payouts_enabled' => (bool)($acct->payouts_enabled ?? false),
            'stripe_connect_email' => $acct->email ?? $tenant->stripe_connect_email,
            'stripe_connect_onboarded' => (bool)($acct->details_submitted ?? false),
        ])->save();
    }

    protected function platformClient(): StripeClient
    {
        // Always use platform secret to manage Connect accounts
        $secret = config('services.stripe.secret');
        return new StripeClient((string)$secret);
    }
}
