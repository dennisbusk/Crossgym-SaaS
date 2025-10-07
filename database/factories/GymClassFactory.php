<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ClassType;
use App\Models\GymClass;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<GymClass>
 */
class GymClassFactory extends Factory
{
    protected $model = GymClass::class;

    public function definition(): array
    {
        $name = [
            'da' => $this->faker->sentence(3),
            'en' => $this->faker->sentence(3),
        ];
        $start = $this->faker->dateTimeBetween('+1 day', '+2 weeks');
        $end = (clone $start)->modify('+1 hour');

        return [
            'tenant_id' => Tenant::factory(),
            'name' => $name,
            'description' => [
                'da' => $this->faker->paragraph(),
                'en' => $this->faker->paragraph(),
            ],
            'trainer_id' => User::factory(),
            'class_type_id' => ClassType::factory(),
            'max_participants' => $this->faker->numberBetween(5, 30),
            'class_start' => $start,
            'class_end' => $end,
            'recurring_id' => null,
        ];
    }
}
