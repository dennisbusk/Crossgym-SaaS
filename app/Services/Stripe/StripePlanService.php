<?php

declare(strict_types=1);

namespace App\Services\Stripe;

use App\Models\Plan;
use Illuminate\Support\Arr;

class StripePlanService
{
    public function __construct(
        protected StripeService $stripe
    ) {}

    public static function make(): self
    {
        return new self(StripeService::forTenant());
    }

    public function getPlans(): array
    {
        return $this->stripe->getPlans();
    }

    public function createPlan(string $name, int $amount, string $interval = 'month', string $currency = 'dkk'): Plan
    {
        $created = $this->stripe->createPlan($name, $amount, $currency, $interval);
        $price = $created['price'];
        $product = $created['product'];

        return Plan::query()->updateOrCreate(
            ['tenant_id' => tenant()?->id, 'stripe_price_id' => $price['id']],
            [
                'name' => $product['name'] ?? $name,
                'amount' => (int)($price['unit_amount'] ?? $amount),
                'currency' => strtoupper($price['currency'] ?? $currency),
                'interval' => Arr::get($price, 'recurring.interval', $interval),
                'metadata' => $product['metadata'] ?? [],
            ]
        );
    }
}
