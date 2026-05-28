<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\WorkoutLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PRService
{
    /**
     * Evaluer om en ny træningslog er en personlig rekord (PR).
     */
    public function evaluatePR(WorkoutLog $workoutLog): array
    {
        $prs = [];
        $user = $workoutLog->user;
        $exerciseId = $workoutLog->exercise_id;

        if (!$exerciseId) {
            return [];
        }

        // 1. Weight PR (Højeste vægt for denne øvelse)
        if ($workoutLog->weight > 0) {
            $maxWeight = WorkoutLog::where('user_id', $user->id)
                ->where('exercise_id', $exerciseId)
                ->where('id', '!=', $workoutLog->id)
                ->max('weight') ?? 0;

            if ($workoutLog->weight > $maxWeight) {
                $prs[] = [
                    'type' => 'weight',
                    'value' => $workoutLog->weight,
                    'previous' => $maxWeight,
                    'label' => __('Weight PR'),
                ];
            }
        }

        // 2. Rep PR (Flest reps ved denne vægt)
        if ($workoutLog->weight > 0 && $workoutLog->reps > 0) {
            $maxRepsAtWeight = WorkoutLog::where('user_id', $user->id)
                ->where('exercise_id', $exerciseId)
                ->where('weight', $workoutLog->weight)
                ->where('id', '!=', $workoutLog->id)
                ->max('reps') ?? 0;

            if ($workoutLog->reps > $maxRepsAtWeight) {
                $prs[] = [
                    'type' => 'reps',
                    'value' => $workoutLog->reps,
                    'previous' => $maxRepsAtWeight,
                    'label' => __('Rep PR'),
                ];
            }
        }

        // 3. Volume PR (Højeste totale volumen for et sæt/log)
        $currentVolume = ($workoutLog->weight ?? 0) * ($workoutLog->reps ?? 0) * ($workoutLog->sets ?? 1);
        if ($currentVolume > 0) {
             $maxVolume = WorkoutLog::where('user_id', $user->id)
                ->where('exercise_id', $exerciseId)
                ->where('id', '!=', $workoutLog->id)
                ->select(DB::raw('MAX(weight * reps * COALESCE(sets, 1)) as max_vol'))
                ->value('max_vol') ?? 0;

            if ($currentVolume > $maxVolume) {
                $prs[] = [
                    'type' => 'volume',
                    'value' => $currentVolume,
                    'previous' => $maxVolume,
                    'label' => __('Volume PR'),
                ];
            }
        }

        return $prs;
    }
}
