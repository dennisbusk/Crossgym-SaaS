<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Achievement;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Achievement>
 */
class AchievementFactory extends Factory
{
    protected $model = Achievement::class;

    public function definition(): array
    {
        $name = fake()->words(3, true);
        return [
            'tenant_id' => Tenant::factory(),
            'slug' => Str::slug($name),
            'name' => ['da' => $name, 'en' => $name],
            'description' => ['da' => fake()->sentence(), 'en' => fake()->sentence()],
            'icon' => 'trophy',
            'type' => fake()->randomElement(['workout', 'checkin', 'social', 'streak']),
            'category' => fake()->randomElement(['general', 'performance', 'consistency']),
            'hidden' => false,
            'repeatable' => false,
            'points' => fake()->numberBetween(10, 100),
            'rarity' => fake()->randomElement(['common', 'uncommon', 'rare', 'epic', 'legendary']),
            'is_active' => true,
        ];
    }
}
