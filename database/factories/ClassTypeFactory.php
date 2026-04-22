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
        $tenant = Tenant::firstOrCreate(
            ['domain' => str_replace(['http://', 'https://'], '', config('app.url'))],
            ['name' => config('app.name', 'Crossgym Saas')]
        );
        $name = [
            'da' => fake()->unique()->words(2, true),
            'en' => fake()->unique()->words(2, true),
        ];
        $slug = Str::slug($name['en']);

        return [
            'tenant_id' => $tenant->id,
            'image' => null,
            'slug' => $slug,
            'name' => $name,
            'description' => [
                'da' => fake()->sentence(),
                'en' => fake()->sentence(),
            ],
        ];
    }
}
