<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'user_id' => User::factory(),
            'stripe_subscription_id' => 'sub_'.fake()->uuid(),
            'stripe_price_id' => Plan::factory(),
            'status' => 'active',
            'current_period_end' => now()->addMonth(),
            'cancel_at_period_end' => false,
            'plan_type' => 'subscription',
            'credits_remaining' => 0,
            'last_credit_reset_at' => now(),
        ];
    }
}
