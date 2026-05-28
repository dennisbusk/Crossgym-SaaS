<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\HealthMetric;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HealthMetric>
 */
class HealthMetricFactory extends Factory
{
    protected $model = HealthMetric::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => fake()->randomElement(['hrv', 'rhr', 'sleep_score', 'weight', 'body_fat']),
            'value' => fake()->randomFloat(2, 40, 100),
            'date' => now()->subDays(fake()->numberBetween(0, 30)),
            'source' => fake()->randomElement(['apple_health', 'google_fit', 'manual']),
        ];
    }
}
