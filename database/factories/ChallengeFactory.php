<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Challenge;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Challenge>
 */
class ChallengeFactory extends Factory
{
    protected $model = Challenge::class;

    public function definition(): array
    {
        $name = fake()->words(3, true);
        return [
            'tenant_id' => Tenant::factory(),
            'name' => ['da' => $name, 'en' => $name],
            'description' => ['da' => fake()->sentence(), 'en' => fake()->sentence()],
            'type' => fake()->randomElement(['workout', 'checkin', 'distance', 'weight']),
            'goal_type' => fake()->randomElement(['count', 'sum', 'min', 'max']),
            'goal_value' => fake()->numberBetween(10, 100),
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'is_active' => true,
        ];
    }
}
