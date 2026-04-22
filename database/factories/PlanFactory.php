<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        $tenant = Tenant::firstOrCreate(
            ['domain' => str_replace(['http://', 'https://'], '', config('app.url'))],
            ['name' => config('app.name', 'Crossgym Saas')]
        );
        $name = $this->faker->unique()->words(2, true);

        return [
            'tenant_id' => $tenant->id,
            'stripe_price_id' => 'price_'.Str::random(16),
            'stripe_product_id' => 'prod_'.Str::random(16),
            'name' => $name,
            'amount' => $this->faker->numberBetween(1000, 10000),
            'currency' => 'DKK',
            'interval' => 'month',
            'metadata' => [
                'plan_type' => 'subscription',
                'weekly_booking_limit' => 3,
                'total_booking_credits' => null,
                'allowed_class_type_ids' => [],
            ],
        ];
    }
}
