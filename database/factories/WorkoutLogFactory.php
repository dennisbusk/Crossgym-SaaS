<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Exercise;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WorkoutLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkoutLog>
 */
class WorkoutLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'user_id' => User::factory(),
            'exercise_id' => Exercise::factory(),
            'date' => fake()->date(),
            'weight' => fake()->randomFloat(2, 50, 150),
            'reps' => fake()->numberBetween(1, 20),
            'sets' => fake()->numberBetween(1, 5),
            'distance' => fake()->randomFloat(2, 1, 10),
            'duration' => fake()->numberBetween(300, 3600),
            'intensity' => fake()->numberBetween(1, 10),
            'mood' => fake()->word(),
            'notes' => fake()->sentence(),
        ];
    }
}
