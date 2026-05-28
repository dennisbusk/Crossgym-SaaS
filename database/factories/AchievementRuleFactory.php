<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Achievement;
use App\Models\AchievementRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AchievementRule>
 */
class AchievementRuleFactory extends Factory
{
    protected $model = AchievementRule::class;

    public function definition(): array
    {
        return [
            'achievement_id' => Achievement::factory(),
            'event' => fake()->randomElement(['workout_completed', 'checkin_completed', 'xp_gained']),
            'operator' => '>=',
            'target' => fake()->numberBetween(1, 100),
            'metadata' => [],
        ];
    }
}
