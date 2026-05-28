<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Challenge;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class ChallengeSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();
        if (!$tenant) return;

        $challenges = [
            [
                'name' => ['da' => 'Sommer Challenge', 'en' => 'Summer Challenge'],
                'description' => ['da' => 'Gennemfør 20 træninger i juni', 'en' => 'Complete 20 workouts in June'],
                'type' => 'workout',
                'goal_type' => 'count',
                'goal_value' => 20,
                'start_date' => now()->startOfMonth(),
                'end_date' => now()->endOfMonth(),
                'is_active' => true,
            ],
            [
                'name' => ['da' => 'Vægttab Konkurrence', 'en' => 'Weight Loss Competition'],
                'description' => ['da' => 'Tab mest muligt vægt', 'en' => 'Lose the most weight'],
                'type' => 'weight',
                'goal_type' => 'sum',
                'goal_value' => 5,
                'start_date' => now()->subDays(10),
                'end_date' => now()->addDays(20),
                'is_active' => true,
            ],
        ];

        foreach ($challenges as $cData) {
            $cData['tenant_id'] = $tenant->id;
            Challenge::firstOrCreate(['name->da' => $cData['name']['da'], 'tenant_id' => $tenant->id], $cData);
        }
    }
}
