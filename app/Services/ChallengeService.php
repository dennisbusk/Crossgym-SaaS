<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Challenge;
use App\Models\User;
use Carbon\Carbon;

class ChallengeService
{
    public function updateProgress(User $user, string $goalType, float $increment): void
    {
        $challenges = Challenge::where('tenant_id', $user->tenant_id)
            ->where('goal_type', $goalType)
            ->where('is_active', true)
            ->where('start_date', '<=', Carbon::today())
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', Carbon::today());
            })
            ->get();

        foreach ($challenges as $challenge) {
            $pivot = $challenge->users()->where('user_id', $user->id)->first()?->pivot;

            if (!$pivot) {
                $challenge->users()->attach($user->id, ['current_value' => $increment]);
                $currentValue = $increment;
            } else {
                $currentValue = $pivot->current_value + $increment;
                $challenge->users()->updateExistingPivot($user->id, [
                    'current_value' => $currentValue
                ]);
            }

            if ($currentValue >= $challenge->goal_value && (!$pivot || !$pivot->completed_at)) {
                $challenge->users()->updateExistingPivot($user->id, [
                    'completed_at' => now()
                ]);
            }
        }
    }
}
