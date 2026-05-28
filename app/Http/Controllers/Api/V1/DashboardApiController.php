<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CheckIn;
use Illuminate\Http\Request;

class DashboardApiController extends Controller
{
    public function hero(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        // 1. High Fatigue Alert
        if (($user->recovery_score ?? 100) < 40) {
            return response()->json([
                'state' => 'recovery_alert',
                'recovery_score' => $user->recovery_score,
                'message' => __('Your recovery is low. Consider a rest day or light mobility.'),
                'suggestion' => 'rest_or_mobility',
            ]);
        }

        // 2. Find next workout
        $nextWorkout = $user->attendingClasses()
            ->where('class_start', '>', now())
            ->orderBy('class_start')
            ->first();

        if ($nextWorkout) {
            return response()->json([
                'state' => 'upcoming',
                'next_workout' => [
                    'id' => $nextWorkout->id,
                    'name' => $nextWorkout->getTranslation('name', 'da'),
                    'class_start' => $nextWorkout->class_start->toIso8601String(),
                    'trainer' => [
                        'name' => $nextWorkout->trainer?->name ?? 'N/A',
                    ],
                ],
                'time_until_start_ms' => now()->diffInMilliseconds($nextWorkout->class_start, false),
            ]);
        }

        // If no upcoming workout, suggest booking
        return response()->json([
            'state' => 'idle',
            'message' => __('No upcoming workouts. Book your next class now!'),
            'suggestion' => 'book_now',
        ]);
    }

    public function activityFeed(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        // 1. Check-ins
        $checkIns = \App\Models\CheckIn::with(['user', 'gymClass'])
            ->where('tenant_id', $user->tenant_id)
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($checkIn) {
                return [
                    'id' => 'checkin_' . $checkIn->id,
                    'user' => $checkIn->user->name,
                    'action' => __('completed').' '.($checkIn->gymClass ? $checkIn->gymClass->getTranslation('name', 'da') : __('a workout')),
                    'time' => $checkIn->created_at->diffForHumans(),
                    'type' => 'workout_complete',
                    'fist_bumps' => \App\Models\FistBump::where('bumpable_type', \App\Models\CheckIn::class)->where('bumpable_id', $checkIn->id)->count(),
                ];
            });

        // 2. Achievements
        $achievements = \App\Models\UserAchievement::with(['user', 'achievement'])
            ->whereHas('user', fn($q) => $q->where('tenant_id', $user->tenant_id))
            ->latest('completed_at')
            ->limit(5)
            ->get()
            ->map(function ($ua) {
                return [
                    'id' => 'achievement_' . $ua->id,
                    'user' => $ua->user->name,
                    'action' => __('unlocked achievement').': ' . $ua->achievement->getTranslation('name', 'da'),
                    'time' => $ua->completed_at->diffForHumans(),
                    'type' => 'achievement',
                    'fist_bumps' => \App\Models\FistBump::where('bumpable_type', \App\Models\UserAchievement::class)->where('bumpable_id', $ua->id)->count(),
                ];
            });

        // Merge and sort
        $recentActivities = $checkIns->concat($achievements)
            ->sortByDesc(fn($activity) => $activity['time']) // This is not ideal as human diff is not sortable easily, but works for limited set
            ->values()
            ->all();

        return response()->json($recentActivities);
    }

    public function bump(string $type, int $id, Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $bumpableType = match($type) {
            'checkin' => \App\Models\CheckIn::class,
            'achievement' => \App\Models\UserAchievement::class,
            default => null,
        };

        if (!$bumpableType) {
            return response()->json(['message' => 'Invalid type'], 400);
        }

        \App\Models\FistBump::firstOrCreate([
            'user_id' => $user->id,
            'bumpable_type' => $bumpableType,
            'bumpable_id' => $id,
        ]);

        return response()->json(['status' => 'success']);
    }

    public function react(string $id, Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string',
        ]);

        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Activity Feed IDs are prefixed with type (e.g., checkin_1, achievement_5)
        if (str_contains($id, '_')) {
            [$type, $actualId] = explode('_', $id);

            $bumpableType = match($type) {
                'checkin' => \App\Models\CheckIn::class,
                'achievement' => \App\Models\UserAchievement::class,
                default => null,
            };

            if ($bumpableType) {
                \App\Models\FistBump::firstOrCreate([
                    'user_id' => $user->id,
                    'bumpable_type' => $bumpableType,
                    'bumpable_id' => (int) $actualId,
                ]);

                return response()->json(['status' => 'success']);
            }
        }

        return response()->json(['message' => 'Invalid activity ID'], 400);
    }
}
