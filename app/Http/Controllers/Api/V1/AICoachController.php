<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\WorkoutLog;
use Illuminate\Http\Request;

class AICoachController extends Controller
{
    public function suggestions(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $recoveryScore = $user->recovery_score ?? 70;

        // Base suggestions based on recovery
        if ($recoveryScore < 40) {
            return response()->json([
                'type' => 'recovery',
                'suggestion' => __('Focus on mobility and active recovery today. Your body needs rest.'),
            ]);
        }

        // Logic for progression suggestion
        $lastWorkout = WorkoutLog::where('user_id', $user->id)
            ->with('exercise')
            ->latest()
            ->first();

        if ($lastWorkout && $lastWorkout->weight > 0) {
            $suggestedWeight = $lastWorkout->weight * 1.025; // 2.5% increase
            return response()->json([
                'type' => 'progression',
                'exercise_id' => $lastWorkout->exercise_id,
                'suggestion' => sprintf(
                    __('Suggested: %.1fkg x %d (Based on last session performance)'),
                    $suggestedWeight,
                    $lastWorkout->reps
                ),
            ]);
        }

        return response()->json([
            'type' => 'general',
            'suggestion' => __('You are ready for a great workout! Focus on good form.'),
        ]);
    }
}
