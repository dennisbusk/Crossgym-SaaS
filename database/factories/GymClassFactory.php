<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ClassType;
use App\Models\GymClass;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<GymClass>
 */
class GymClassFactory extends Factory
{
    protected $model = GymClass::class;

    public function nearestFifteen($time)
    {
        $time = Carbon::parse($time);

        // Round to nearest 15 minutes
        $rounded = $time->copy()->setTime(
            $time->hour,
            (int) round($time->minute / 15) * 15,
            0
        );

        // Fix if minute becomes 60
        if ($rounded->minute === 60) {
            $rounded->addHour()->minute(0);
        }

        return $rounded;

    }

    public function definition(): array
    {
        $tenant = Tenant::firstOrCreate(
            ['domain' => str_replace(['http://', 'https://'], '', config('app.url'))],
            ['name' => config('app.name', 'Crossgym Saas')]
        );
        $name = [
            'da' => $this->faker->sentence(3),
            'en' => $this->faker->sentence(3),
        ];
        $start = $this->nearestFifteen($this->faker->dateTimeBetween('-2 month', '+12 months'));
        $end = (clone $start)->modify('+1 hour');

        return [
            'tenant_id' => $tenant->id,
            'name' => $name,
            'description' => [
                'da' => $this->faker->paragraph(),
                'en' => $this->faker->paragraph(),
            ],
            'trainer_id' => User::where('role_id', Role::where('slug', 'trainer')->where('tenant_id', $tenant->id)->value('id'))->where('tenant_id', $tenant->id)->inRandomOrder()->value('id'),
            'class_type_id' => ClassType::inRandomOrder()->value('id'),
            'max_participants' => $this->faker->numberBetween(5, 30),
            'class_start' => $start,
            'class_end' => $end,
            'recurring_id' => null,
        ];
    }

    public function withTenant(int $tenantId): static
    {
        return $this->state(fn () => ['tenant_id' => $tenantId]);
    }

    public function withType(int $typeId): static
    {
        return $this->state(fn () => ['class_type_id' => $typeId]);
    }

    // Optional: a variant to customize the number of items when needed
    public function withParent($class_id): static
    {
        return $this->state(fn () => ['recurring_id' => $class_id]);
    }
}
