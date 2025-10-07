<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ClassType;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ClassType>
 */
class ClassTypeFactory extends Factory
{
    protected $model = ClassType::class;

    public function definition(): array
    {
        $name = [
            'da' => $this->faker->unique()->words(2, true),
            'en' => $this->faker->unique()->words(2, true),
        ];
        $slug = Str::slug($name['en']);

        return [
            'tenant_id' => Tenant::factory(),
            'color' => $this->faker->safeHexColor(),
            'image' => null,
            'slug' => $slug,
            'name' => $name,
            'description' => [
                'da' => $this->faker->sentence(),
                'en' => $this->faker->sentence(),
            ],
        ];
    }
}
