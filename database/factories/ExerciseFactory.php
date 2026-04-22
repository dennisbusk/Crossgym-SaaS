<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Exercise;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Exercise>
 */
class ExerciseFactory extends Factory
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
            'name' => ['da' => $this->faker->word()],
            'category' => $this->faker->randomElement(['strength', 'cardio', 'biometric']),
        ];
    }
}
