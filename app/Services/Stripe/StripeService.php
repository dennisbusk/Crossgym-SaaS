<?php

declare(strict_types=1);

namespace App\Services\Stripe;

use App\Models\Tenant;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Throwable;

class StripeService
{
    public function __construct(
        protected StripeTenantClient $tenantClient
    ) {}

    public static function forTenant(?Tenant $tenant = null): self
    {
        return new self(new StripeTenantClient($tenant));
    }

    // 1. Create Payment using Hosted Checkout Session (one-time)
    public function createPayment(array $params): array
    {
        $client = $this->tenantClient->client();
        $opts = $this->tenantClient->options();

        // Minimal hosted checkout session
        $session = $client->checkout->sessions->create([
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'line_items' => $params['line_items'] ?? [],
            'success_url' => $params['success_url'] ?? url('/payments/success'),
            'cancel_url' => $params['cancel_url'] ?? url('/payments/cancel'),
            'customer_email' => $params['customer_email'] ?? null,
            'metadata' => array_merge((array)($params['metadata'] ?? []), [
                'tenant_id' => tenant()?->id,
                'user_id' => $params['user_id'] ?? auth()->id(),
            ]),
        ], $opts);

        return [
            'id' => $session->id,
            'payment_intent_id' => $session->payment_intent ?? null,
            'status' => $session->status,
            'url' => $session->url,
            'raw' => $session->toArray(),
        ];
    }

    // 2. Capture Payment
    public function capturePayment(string $paymentIntentId): array
    {
        $client = $this->tenantClient->client();
        $opts = $this->tenantClient->options();
        $pi = $client->paymentIntents->capture($paymentIntentId, [], $opts);
        return $pi->toArray();
    }

    // 3. Refund full payment
    public function refundPayment(string $paymentIntentId): array
    {
        $client = $this->tenantClient->client();
        $opts = $this->tenantClient->options();
        $refund = $client->refunds->create([
            'payment_intent' => $paymentIntentId,
        ], $opts);
        return $refund->toArray();
    }

    // 4. Partial refund
    public function partialRefund(string $paymentIntentId, int $amount, string $currency = 'dkk', ?array $meta = null): array
    {
        $client = $this->tenantClient->client();
        $opts = $this->tenantClient->options();
        $refund = $client->refunds->create([
            'payment_intent' => $paymentIntentId,
            'amount' => $amount,
            'metadata' => $meta ?? [],
        ], $opts);

        // TODO: persist to payment_refunds table via model
        try {
            \DB::table('payment_refunds')->insert([
                'tenant_id' => tenant()?->id,
                'payment_intent_id' => $paymentIntentId,
                'amount' => $amount,
                'currency' => strtoupper($currency),
                'reason' => $meta['reason'] ?? null,
                'meta' => $meta ? json_encode($meta) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (Throwable $e) {
            Log::warning('Failed to store partial refund record', ['error' => $e->getMessage()]);
        }

        return $refund->toArray();
    }

    // 5. Create Subscription via Hosted Checkout
    public function createSubscription(array $params): array
    {
        $client = $this->tenantClient->client();
        $opts = $this->tenantClient->options();

        $session = $client->checkout->sessions->create([
            'mode' => 'subscription',
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price' => $params['price_id'],
                'quantity' => 1,
            ]],
            'success_url' => $params['success_url'] ?? url('/subscriptions/success'),
            'cancel_url' => $params['cancel_url'] ?? url('/subscriptions/cancel'),
            'customer_email' => $params['customer_email'] ?? null,
            'metadata' => array_merge((array)($params['metadata'] ?? []), [
                'tenant_id' => tenant()?->id,
                'user_id' => $params['user_id'] ?? auth()->id(),
            ]),
        ], $opts);

        return [
            'id' => $session->id,
            'subscription' => $session->subscription ?? null,
            'status' => $session->status,
            'url' => $session->url,
            'raw' => $session->toArray(),
        ];
    }

    // 6. Swap Subscription
    public function swapSubscription(string $subscriptionId, string $newPriceId): array
    {
        $client = $this->tenantClient->client();
        $opts = $this->tenantClient->options();

        $subscription = $client->subscriptions->retrieve($subscriptionId, [], $opts);
        $currentItemId = $subscription->items->data[0]->id;

        $updated = $client->subscriptions->update($subscriptionId, [
            'items' => [[
                'id' => $currentItemId,
                'price' => $newPriceId,
            ]],
            'proration_behavior' => 'create_prorations',
        ], $opts);

        return $updated->toArray();
    }

    // 7. Cancel Subscription
    public function cancelSubscription(string $subscriptionId, bool $atPeriodEnd = true): array
    {
        $client = $this->tenantClient->client();
        $opts = $this->tenantClient->options();

        $result = $client->subscriptions->update($subscriptionId, [
            'cancel_at_period_end' => $atPeriodEnd,
        ], $opts);

        return $result->toArray();
    }

    // 8. Get Plans (Products + Prices)
    public function getPlans(): array
    {
        $client = $this->tenantClient->client();
        $opts = $this->tenantClient->options();
        $products = $client->products->all(['active' => true], $opts);
        $prices = $client->prices->all(['active' => true, 'expand' => ['data.product']], $opts);
        return [
            'products' => $products->toArray(),
            'prices' => $prices->toArray(),
        ];
    }

    // 9. Create Plan (Product + Price)
    public function createPlan(string $name, int $unitAmount, string $currency = 'dkk', string $interval = 'month'): array
    {
        $client = $this->tenantClient->client();
        $opts = $this->tenantClient->options();
        $product = $client->products->create(['name' => $name], $opts);
        $price = $client->prices->create([
            'product' => $product->id,
            'unit_amount' => $unitAmount,
            'currency' => strtolower($currency),
            'recurring' => [
                'interval' => $interval,
            ],
        ], $opts);

        return [
            'product' => $product->toArray(),
            'price' => $price->toArray(),
        ];
    }
}
