<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::with('role');

        if ($request->has('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }

        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }

        $users = $query->get();

        return UserResource::collection($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', Password::defaults()],
            'role_id' => 'required|exists:roles,id',
            'tenant_id' => 'required|exists:tenants,id',
            // Andre felter kan tilføjes efter behov
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        return new UserResource($user->load('role'));
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return new UserResource($user->load('role'));
    }

    public function stats(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        return response()->json([
            'xp' => $user->xp ?? 0,
            'level' => $user->level ?? 1,
            'recovery_score' => $user->recovery_score ?? 100,
            'streak_days' => $user->achievementProgress()
                ->whereHas('achievement', fn ($q) => $q->where('type', 'streak'))
                ->max('progress') ?? 0,
            'total_workouts' => \App\Models\CheckIn::where('user_id', $user->id)->count(),
            'monthly_consistency_percent' => 85, // Placeholder for now
        ]);
    }

    public function attendance(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $months = (int) ($request->months ?? 6);

        // Simple placeholder for consistency bar chart
        $data = [];
        for ($i = 0; $i < $months; $i++) {
            $data[] = rand(30, 95);
        }

        return response()->json([
            'data' => $data,
        ]);
    }

    public function achievements(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $achievements = \App\Models\Achievement::with('rules')
            ->where('is_active', true)
            ->where('hidden', false)
            ->get();

        $userAchievements = $user->achievements()->pluck('achievement_id')->toArray();
        $userProgress = $user->achievementProgress()->get()->keyBy('achievement_id');

        $result = $achievements->map(function ($achievement) use ($userAchievements, $userProgress) {
            $progress = $userProgress->get($achievement->id);
            $isUnlocked = in_array($achievement->id, $userAchievements);

            return [
                'id' => $achievement->id,
                'slug' => $achievement->slug,
                'title' => $achievement->name,
                'description' => $achievement->description,
                'icon' => $achievement->icon,
                'points' => $achievement->points,
                'rarity' => $achievement->rarity,
                'unlocked' => $isUnlocked,
                'unlocked_at' => $isUnlocked ? $achievement->pivot?->completed_at : null,
                'progress' => $progress ? $progress->progress : 0,
                'target' => (int) ($achievement->rules->first()?->target ?? 1),
            ];
        });

        return response()->json($result);
    }

    public function walletPass(Request $request)
    {
        // For now, return a placeholder or a simple response
        return response()->json(['message' => __('Wallet pass generation is not fully implemented yet')], 501);
    }

    public function syncDeviceData(Request $request)
    {
        $validated = $request->validate([
            'source' => 'required|string',
            'metrics' => 'required|array',
            'metrics.*.type' => 'required|string',
            'metrics.*.value' => 'required|numeric',
            'metrics.*.date' => 'required|date',
        ]);

        /** @var \App\Models\User $user */
        $user = auth()->user();

        app(\App\Services\HealthSyncService::class)->sync($user, $validated);

        return response()->json([
            'status' => 'success',
            'message' => __('Data synced successfully'),
            'recovery_score' => $user->fresh()->recovery_score,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,'.$user->id,
            'password' => ['sometimes', Password::defaults()],
            'role_id' => 'sometimes|exists:roles,id',
            'tenant_id' => 'sometimes|exists:tenants,id',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return new UserResource($user->load('role'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(null, 204);
    }
}
