<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'domain' => Str::slug($name).'.example.test',
            'app_name' => $name,
            'theme_color' => '#3b82f6',
            'background_color' => '#ffffff',
            'terms' => ['da' => fake()->paragraph()],
            'onboarded_at' => now(),
            'allow_member_billing_management' => true,
        ];
    }
}
