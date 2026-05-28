<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ClassTypeSeeder::class,
            PermissionSeeder::class,
            AchievementSeeder::class,
            ChallengeSeeder::class,
        ]);

        $tenant = \App\Models\Tenant::first();
        if ($tenant) {
            // Create some colors
            \App\Models\Color::factory()->count(10)->create(['tenant_id' => $tenant->id]);

            // Create some plans
            \App\Models\Plan::factory()->count(3)->create(['tenant_id' => $tenant->id]);

            // Create some exercises
            \App\Models\Exercise::factory()->count(20)->create(['tenant_id' => $tenant->id]);

            // Create some more users (members)
            $memberRole = \App\Models\Role::where('slug', 'member')->where('tenant_id', $tenant->id)->first();
            if ($memberRole) {
                \App\Models\User::factory()->count(10)->create([
                    'tenant_id' => $tenant->id,
                    'role_id' => $memberRole->id,
                ])->each(function ($user) use ($tenant) {
                    // Give them a subscription
                    \App\Models\Subscription::factory()->create([
                        'user_id' => $user->id,
                        'tenant_id' => $tenant->id,
                    ]);

                    // Add some health metrics (ensuring unique dates for the same type)
                    $types = ['hrv', 'rhr', 'sleep_score', 'weight', 'body_fat'];
                    foreach ($types as $type) {
                        \App\Models\HealthMetric::factory()->create([
                            'user_id' => $user->id,
                            'type' => $type,
                            'date' => now()->subDays(fake()->unique()->numberBetween(0, 30)),
                        ]);
                    }
                    fake()->unique(true); // Reset unique generator for next user

                    // Add some workout logs
                    \App\Models\WorkoutLog::factory()->count(3)->create([
                        'user_id' => $user->id,
                        'tenant_id' => $tenant->id,
                    ]);
                });
            }

            // Create some classes
            \App\Models\GymClass::factory()->count(10)->create([
                'tenant_id' => $tenant->id,
            ]);
        }
    }
}
