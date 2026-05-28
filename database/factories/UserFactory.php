<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tenant = Tenant::firstOrCreate(
            ['domain' => str_replace(['http://', 'https://'], '', config('app.url'))],
            ['name' => config('app.name', 'Crossgym Saas')]
        );

        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'two_factor_secret' => Str::random(10),
            'two_factor_recovery_codes' => Str::random(10),
            'two_factor_confirmed_at' => now(),
            'terms_accepted_at' => now(),
            'tenant_id' => $tenant->id,
            'xp' => fake()->numberBetween(0, 1000),
            'level' => fake()->numberBetween(1, 10),
            'medlemsnummer' => fake()->unique()->numberBetween(1000, 9999),
            'address' => fake()->address(),
            'postal_code' => fake()->postcode(),
            'city' => fake()->city(),
            'birthday' => fake()->date(),
            'phone' => fake()->phoneNumber(),
            'mobile' => fake()->phoneNumber(),
            'sex' => fake()->randomElement(['M', 'F', 'Other']),
            'joined_at' => now(),
            'recovery_score' => fake()->numberBetween(0, 100),
            'last_hrv' => fake()->numberBetween(20, 100),
            'last_rhr' => fake()->numberBetween(40, 100),
            'last_sleep_score' => fake()->numberBetween(0, 100),
            'recovery_updated_at' => now(),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model does not have two-factor authentication configured.
     */
    public function withoutTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);
    }

    public function withTenant(int $tenantId): static
    {
        return $this->state(fn () => ['tenant_id' => $tenantId]);
    }
}
