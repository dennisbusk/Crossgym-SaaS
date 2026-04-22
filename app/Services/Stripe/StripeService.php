<?php

declare(strict_types=1);

namespace App\Services\Stripe;

use App\Models\Tenant;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\RateLimitException;
use Throwable;

class StripeService
{
    private const RETRY_ATTEMPTS = 2;

    private const RETRY_DELAY_SECONDS = 1;

    public function __construct(
        protected StripeTenantClient $tenantClient
    ) {}

    /**
     * Execute a Stripe API call with retry on rate limit (429).
     */
    private function withRetry(callable $callback, array $context = []): mixed
    {
        $lastException = null;
        for ($attempt = 0; $attempt <= self::RETRY_ATTEMPTS; $attempt++) {
            try {
                return $callback();
            } catch (RateLimitException $e) {
                $lastException = $e;
                Log::channel('stripe')->warning('Stripe rate limit hit, retrying', array_merge($context, [
                    'attempt' => $attempt + 1,
                    'error' => $e->getMessage(),
                ]));
                if ($attempt < self::RETRY_ATTEMPTS) {
                    sleep(self::RETRY_DELAY_SECONDS);
                }
            } catch (Throwable $e) {
                Log::channel('stripe')->error('Stripe API error', array_merge($context, [
                    'error' => $e->getMessage(),
                ]));
                throw $e;
            }
        }
        throw $lastException;
    }

    public function updateConnectedAccountMetadata(Tenant $tenant, array $metadata): array
    {
        return $this->withRetry(function () use ($tenant, $metadata) {
            $client = new \Stripe\StripeClient(config('services.stripe.secret'));
            $accountId = (string) $tenant->stripe_connect_account_id;
            $normalized = $this->normalizeMetadata($metadata);
            $account = $client->accounts->update($accountId, [
                'metadata' => $normalized,
            ]);

            return $account->toArray();
        }, ['tenant_id' => $tenant->id, 'method' => 'updateConnectedAccountMetadata']);
    }

    public static function forTenant(?Tenant $tenant = null): self
    {
        return app(self::class, ['tenantClient' => app(StripeTenantClient::class, ['tenant' => $tenant])]);
    }

    // 1. Create Payment using Hosted Checkout Session (one-time)
    public function createPayment(array $params): array
    {
        return $this->withRetry(function () use ($params) {
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
                // Show a "Continue" button on the Checkout success page
                // See: https://stripe.com/docs/payments/checkout/custom-success-page#after-completion
                'after_completion' => [
                    'type' => 'redirect',
                    'redirect' => [
                        'url' => $params['continue_url']
                            ?? ($params['success_url'] ?? url('/')), // fallback to success_url or home
                    ],
                ],
                'metadata' => array_merge((array) $params['metadata'] ?? [], [
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
        }, ['method' => 'createPayment']);
    }

    public function createSubscriptionCheckout(array $params): array
    {
        return $this->withRetry(function () use ($params) {
            $client = $this->tenantClient->client();
            $opts = $this->tenantClient->options();

            $sessionData = [
                'mode' => 'subscription',
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price' => $params['price_id'],
                    'quantity' => 1,
                ]],
                'success_url' => $params['success_url'],
                'cancel_url' => $params['cancel_url'],
                'metadata' => array_merge((array) ($params['metadata'] ?? []), [
                    'tenant_id' => tenant()?->id,
                    'user_id' => $params['user_id'] ?? auth()->id(),
                ]),
                'subscription_data' => [
                    'metadata' => array_merge((array) ($params['metadata'] ?? []), [
                        'tenant_id' => tenant()?->id,
                        'user_id' => $params['user_id'] ?? auth()->id(),
                    ]),
                ],
            ];

            if (isset($params['customer_id'])) {
                $sessionData['customer'] = $params['customer_id'];
            } else {
                $sessionData['customer_email'] = $params['customer_email'] ?? null;
            }

            $session = $client->checkout->sessions->create($sessionData, $opts);

            return [
                'id' => $session->id,
                'url' => $session->url,
            ];
        }, ['method' => 'createSubscriptionCheckout']);
    }

    // 2. Capture Payment
    public function capturePayment(string $paymentIntentId): array
    {
        return $this->withRetry(function () use ($paymentIntentId) {
            $client = $this->tenantClient->client();
            $opts = $this->tenantClient->options();
            $pi = $client->paymentIntents->capture($paymentIntentId, [], $opts);

            return $pi->toArray();
        }, ['payment_intent_id' => $paymentIntentId, 'method' => 'capturePayment']);
    }

    // 3. Refund full payment
    public function refundPayment(string $paymentIntentId): array
    {
        return $this->withRetry(function () use ($paymentIntentId) {
            $client = $this->tenantClient->client();
            $opts = $this->tenantClient->options();
            $refund = $client->refunds->create([
                'payment_intent' => $paymentIntentId,
            ], $opts);

            return $refund->toArray();
        }, ['payment_intent_id' => $paymentIntentId, 'method' => 'refundPayment']);
    }

    // 4. Partial refund
    public function partialRefund(string $paymentIntentId, int $amount, string $currency = 'dkk', ?array $meta = null): array
    {
        $refund = $this->withRetry(function () use ($paymentIntentId, $amount, $meta) {
            $client = $this->tenantClient->client();
            $opts = $this->tenantClient->options();

            return $client->refunds->create([
                'payment_intent' => $paymentIntentId,
                'amount' => $amount,
                'metadata' => $meta ?? [],
            ], $opts);
        }, ['payment_intent_id' => $paymentIntentId, 'method' => 'partialRefund']);

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
            Log::channel('stripe')->warning('Failed to store partial refund record', [
                'payment_intent_id' => $paymentIntentId,
                'tenant_id' => tenant()?->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $refund->toArray();
    }

    // 5. Create Subscription via Hosted Checkout
    public function createSubscription(array $params): array
    {
        return $this->withRetry(function () use ($params) {
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
                'metadata' => array_merge((array) $params['metadata'] ?? [], [
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
        }, ['method' => 'createSubscription']);
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
        return $this->withRetry(function () {
            $client = $this->tenantClient->client();
            $opts = $this->tenantClient->options();
            $products = $client->products->all(['active' => true], $opts);
            $prices = $client->prices->all(['active' => true, 'expand' => ['data.product']], $opts);

            return [
                'products' => $products->toArray(),
                'prices' => $prices->toArray(),
            ];
        }, ['method' => 'getPlans']);
    }

    // 9. Create Plan (Product + Price)
    public function createPlan(string $name, int $unitAmount, string $currency = 'dkk', string $interval = 'month', array $metadata = []): array
    {
        $client = $this->tenantClient->client();
        $opts = $this->tenantClient->options();
        $metadata = $this->normalizeMetadata($metadata);
        $product = $client->products->create([
            'name' => $name,
            'metadata' => $metadata,
        ], $opts);

        $priceParams = [
            'product' => $product->id,
            'unit_amount' => $unitAmount,
            'currency' => strtolower($currency),
            'metadata' => $metadata,
        ];
        if ($interval !== 'one_time') {
            $priceParams['recurring'] = [
                'interval' => $interval,
            ];
        }
        $price = $client->prices->create($priceParams, $opts);

        return [
            'product' => $product->toArray(),
            'price' => $price->toArray(),
        ];
    }

    // 9b. Update Product (name/metadata)
    public function updateProduct(string $productId, string $name, array $metadata = []): array
    {
        $client = $this->tenantClient->client();
        $opts = $this->tenantClient->options();
        $metadata = $this->normalizeMetadata($metadata);
        $product = $client->products->update($productId, [
            'name' => $name,
            'metadata' => $metadata,
        ], $opts);

        return $product->toArray();
    }

    // 9c. Create a new Price for an existing Product
    public function createPrice(string $productId, int $unitAmount, string $currency = 'dkk', string $interval = 'month', array $metadata = []): array
    {
        $client = $this->tenantClient->client();
        $opts = $this->tenantClient->options();
        $metadata = $this->normalizeMetadata($metadata);
        $params = [
            'product' => $productId,
            'unit_amount' => $unitAmount,
            'currency' => strtolower($currency),
            'metadata' => $metadata,
        ];
        if ($interval !== 'one_time') {
            $params['recurring'] = [
                'interval' => $interval,
            ];
        }
        $price = $client->prices->create($params, $opts);

        return $price->toArray();
    }

    private function normalizeMetadata(array $metadata): array
    {
        $normalized = [];
        foreach ($metadata as $key => $value) {
            if (is_array($value)) {
                $normalized[$key] = json_encode($value);
            } elseif (is_bool($value)) {
                $normalized[$key] = $value ? 'true' : 'false';
            } elseif (is_null($value)) {
                // Stripe ignores nulls; skip
                continue;
            } else {
                $normalized[$key] = (string) $value;
            }
        }

        return $normalized;
    }

    // 10. Create PaymentIntent using Destination Charges model (platform handles payments)
    public function createDestinationChargeIntent(Tenant $tenant, int $amount, string $currency = 'dkk', array $params = []): array
    {
        return $this->withRetry(function () use ($tenant, $amount, $currency, $params) {
            // Use platform secret
            $client = new \Stripe\StripeClient(config('services.stripe.secret'));

            $pi = $client->paymentIntents->create(array_merge([
                'amount' => $amount,
                'currency' => strtolower($currency),
                'payment_method_types' => ['card'],
                'transfer_data' => [
                    'destination' => (string) $tenant->stripe_connect_account_id,
                ],
                'metadata' => array_merge([
                    'tenant_id' => $tenant->id,
                    'user_id' => auth()->id(),
                ], $params['metadata'] ?? []),
            ], $params));

            return $pi->toArray();
        }, ['tenant_id' => $tenant->id, 'method' => 'createDestinationChargeIntent']);
    }
}
