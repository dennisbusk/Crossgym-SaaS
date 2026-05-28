<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\HealthMetric;
use App\Models\User;
use App\Models\WorkoutLog;
use Carbon\Carbon;

class RecoveryService
{
    public function calculateForUser(User $user): int
    {
        $today = Carbon::today();

        // 1. Sleep Score (40%)
        $sleepScore = $this->getSleepScore($user, $today);

        // 2. HRV Status (30%)
        $hrvScore = $this->getHrvScore($user, $today);

        // 3. Training Load & Recovery (30%)
        $trainingScore = $this->getTrainingRecoveryScore($user, $today);

        $totalScore = ($sleepScore * 0.4) + ($hrvScore * 0.3) + ($trainingScore * 0.3);

        $finalScore = (int) round($totalScore);

        $user->update([
            'recovery_score' => $finalScore,
            'recovery_updated_at' => now(),
        ]);

        return $finalScore;
    }

    protected function getSleepScore(User $user, Carbon $date): int
    {
        $metric = HealthMetric::where('user_id', $user->id)
            ->where('date', $date)
            ->where('type', 'sleep_quality')
            ->first();

        if ($metric) {
            $user->update(['last_sleep_score' => (int) $metric->value]);
            return (int) $metric->value;
        }

        return 70; // Default baseline
    }

    protected function getHrvScore(User $user, Carbon $date): int
    {
        $metric = HealthMetric::where('user_id', $user->id)
            ->where('date', $date)
            ->where('type', 'hrv')
            ->first();

        if ($metric) {
            $user->update(['last_hrv' => (int) $metric->value]);
            // Logic: Compare with user's baseline. For now, simple 0-100 mapping.
            return (int) min(100, ($metric->value * 1.5));
        }

        return 70;
    }

    protected function getTrainingRecoveryScore(User $user, Carbon $date): int
    {
        // Calculate load from last 3 days
        $recentLogs = WorkoutLog::where('user_id', $user->id)
            ->where('date', '>=', $date->copy()->subDays(3))
            ->get();

        $load = 0;
        foreach ($recentLogs as $log) {
            $intensity = $log->intensity ?? 5;
            $load += $intensity * 10;
        }

        // Max theoretical load for 3 days could be 300.
        // Recovery score is inverse of load.
        $score = 100 - ($load / 3);

        return (int) max(0, min(100, $score));
    }
}
