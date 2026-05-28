<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Achievement;
use App\Models\AchievementRule;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AchievementSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();
        if (!$tenant) return;

        $achievements = [
            [
                'name' => ['da' => 'Første træning', 'en' => 'First Workout'],
                'description' => ['da' => 'Gennemfør din første træning', 'en' => 'Complete your first workout'],
                'slug' => 'first-workout',
                'type' => 'workout',
                'points' => 50,
                'rules' => [
                    ['event' => 'workout_completed', 'operator' => '>=', 'target' => 1]
                ]
            ],
            [
                'name' => ['da' => 'Træningsmaskine', 'en' => 'Workout Machine'],
                'description' => ['da' => 'Gennemfør 10 træninger', 'en' => 'Complete 10 workouts'],
                'slug' => 'workout-machine',
                'type' => 'workout',
                'points' => 200,
                'rules' => [
                    ['event' => 'workout_completed', 'operator' => '>=', 'target' => 10]
                ]
            ],
            [
                'name' => ['da' => 'Morgenfugl', 'en' => 'Early Bird'],
                'description' => ['da' => 'Tjek ind før kl. 08:00', 'en' => 'Check in before 08:00 AM'],
                'slug' => 'early-bird',
                'type' => 'checkin',
                'points' => 100,
                'rules' => [
                    ['event' => 'checkin_completed', 'operator' => '>=', 'target' => 1, 'metadata' => ['before' => '08:00']]
                ]
            ],
        ];

        foreach ($achievements as $aData) {
            $rules = $aData['rules'];
            unset($aData['rules']);

            $aData['tenant_id'] = $tenant->id;
            $achievement = Achievement::firstOrCreate(['slug' => $aData['slug'], 'tenant_id' => $tenant->id], $aData);

            foreach ($rules as $rData) {
                AchievementRule::firstOrCreate([
                    'achievement_id' => $achievement->id,
                    'event' => $rData['event'],
                    'target' => $rData['target']
                ], $rData);
            }
        }
    }
}
