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
            'date' => $this->faker->date(),
            'weight' => $this->faker->randomFloat(2, 50, 150),
            'reps' => $this->faker->numberBetween(1, 20),
            'sets' => $this->faker->numberBetween(1, 5),
            'distance' => $this->faker->randomFloat(2, 1, 10),
            'duration' => $this->faker->numberBetween(300, 3600),
            'intensity' => $this->faker->numberBetween(1, 10),
            'mood' => $this->faker->word(),
            'notes' => $this->faker->sentence(),
        ];
    }
}
