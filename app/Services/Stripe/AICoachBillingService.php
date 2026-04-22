<?php

declare(strict_types=1);

namespace App\Services\Stripe;

use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;
use Stripe\Checkout\Session;
use Stripe\StripeClient;

class AICoachBillingService
{
    private const PRICE_CACHE_TTL = 3600; // 1 hour

    public function __construct(
        protected StripeClient $client = new StripeClient(config('services.stripe.secret'))
    ) {}

    /**
     * Resolve the Stripe price ID for the given interval (monthly or yearly).
     * Fetches active prices from the AI Coach product and caches the result.
     */
    public function getPriceIdForInterval(string $interval): ?string
    {
        $productId = config('services.stripe.ai_coach_product_id');
        if (! $productId) {
            return null;
        }

        $cacheKey = "ai_coach_price_{$interval}";

        return Cache::remember($cacheKey, self::PRICE_CACHE_TTL, function () use ($productId, $interval) {
            $stripeInterval = $interval === 'yearly' ? 'year' : 'month';

            $prices = $this->client->prices->all([
                'product' => $productId,
                'active' => true,
            ]);

            foreach ($prices->data as $price) {
                if (($price->recurring->interval ?? null) === $stripeInterval) {
                    return $price->id;
                }
            }

            return null;
        });
    }

    public function createCheckoutSession(Tenant $tenant, string $priceId, string $customerEmail, string $successUrl, string $cancelUrl): Session
    {
        return $this->client->checkout->sessions->create([
            'mode' => 'subscription',
            'line_items' => [
                [
                    'price' => $priceId,
                    'quantity' => 1,
                ],
            ],
            'customer_email' => $customerEmail,
            'metadata' => [
                'tenant_id' => (string) $tenant->id,
                'type' => 'ai_coach',
            ],
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ]);
    }

    public function createBillingPortalSession(string $customerId, string $returnUrl): \Stripe\BillingPortal\Session
    {
        return $this->client->billingPortal->sessions->create([
            'customer' => $customerId,
            'return_url' => $returnUrl,
        ]);
    }

    public function getSubscription(string $subscriptionId): ?\Stripe\Subscription
    {
        try {
            return $this->client->subscriptions->retrieve($subscriptionId);
        } catch (\Throwable) {
            return null;
        }
    }

    public function cancelSubscriptionAtPeriodEnd(string $subscriptionId): \Stripe\Subscription
    {
        return $this->client->subscriptions->update($subscriptionId, [
            'cancel_at_period_end' => true,
        ]);
    }
}
