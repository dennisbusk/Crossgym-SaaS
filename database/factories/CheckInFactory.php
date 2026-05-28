<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CheckIn;
use App\Models\GymClass;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CheckIn>
 */
class CheckInFactory extends Factory
{
    protected $model = CheckIn::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'tenant_id' => Tenant::factory(),
            'is_paid' => true,
            'charge_id' => 'ch_'.fake()->uuid(),
            'checked_at' => now(),
            'gym_class_id' => GymClass::factory(),
        ];
    }
}
