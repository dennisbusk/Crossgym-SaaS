<?php

declare(strict_types=1);

namespace App\Services\Stripe;

use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Support\Arr;

class StripePlanService
{
    public function __construct(
        protected StripeService $stripe
    ) {}

    public static function make(?Tenant $tenant = null): self
    {
        return new self(StripeService::forTenant($tenant));
    }

    public function getPlans(): array
    {
        return $this->stripe->getPlans();
    }

    public function createPlan(string $name, int $amount, string $interval = 'month', string $currency = 'dkk', array $metadata = []): Plan
    {
        $created = $this->stripe->createPlan($name, $amount, $currency, $interval, $metadata);
        $price = $created['price'];
        $product = $created['product'];

        return Plan::query()->updateOrCreate(
            ['tenant_id' => tenant()?->id, 'stripe_price_id' => $price['id']],
            [
                'name' => $product['name'] ?? $name,
                'amount' => (int) ($price['unit_amount'] ?? $amount),
                'currency' => strtoupper($price['currency'] ?? $currency),
                'interval' => Arr::get($price, 'recurring.interval', $interval) ?? 'one_time',
                'metadata' => $product['metadata'] ?? $metadata,
                'stripe_product_id' => $product['id'] ?? null,
            ]
        );
    }

    public function updatePlan(Plan $plan, string $name, int $amount, string $currency = 'DKK', string $interval = 'month', array $metadata = []): Plan
    {
        // 1) Update product name/metadata
        if ($plan->stripe_product_id) {
            $this->stripe->updateProduct($plan->stripe_product_id, $name, $metadata);
        }

        $needsNewPrice = false;
        $currentInterval = (string) ($plan->interval ?? 'month');
        $currentAmount = (int) ($plan->amount ?? 0);
        $currentCurrency = strtoupper((string) ($plan->currency ?? 'DKK'));

        if ($currentAmount !== $amount || strtoupper($currency) !== $currentCurrency || $currentInterval !== $interval) {
            $needsNewPrice = true;
        }

        if ($needsNewPrice) {
            $productId = $plan->stripe_product_id;
            if (! $productId) {
                // create product first
                $created = $this->stripe->createPlan($name, $amount, $currency, $interval, $metadata);
                $plan->stripe_product_id = $created['product']['id'] ?? null;
                $plan->stripe_price_id = $created['price']['id'] ?? null;
                $plan->amount = (int) ($created['price']['unit_amount'] ?? $amount);
                $plan->currency = strtoupper($created['price']['currency'] ?? $currency);
                $plan->interval = Arr::get($created, 'price.recurring.interval', $interval) ?? 'one_time';
                $plan->metadata = $created['product']['metadata'] ?? $metadata;
                $plan->name = $created['product']['name'] ?? $name;
                $plan->save();

                return $plan;
            }

            $price = $this->stripe->createPrice($productId, $amount, $currency, $interval, $metadata);
            $plan->stripe_price_id = $price['id'] ?? $plan->stripe_price_id;
            $plan->amount = (int) ($price['unit_amount'] ?? $amount);
            $plan->currency = strtoupper($price['currency'] ?? $currency);
            $plan->interval = Arr::get($price, 'recurring.interval', $interval) ?? 'one_time';
        }

        // Always sync base fields and metadata/name
        $plan->name = $name;
        $plan->metadata = $metadata;
        $plan->save();

        return $plan;
    }
}
