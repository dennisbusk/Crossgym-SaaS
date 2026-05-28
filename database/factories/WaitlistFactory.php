<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\GymClass;
use App\Models\User;
use App\Models\Waitlist;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Waitlist>
 */
class WaitlistFactory extends Factory
{
    protected $model = Waitlist::class;

    public function definition(): array
    {
        return [
            'gym_class_id' => GymClass::factory(),
            'user_id' => User::factory(),
        ];
    }
}
