<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Achievement;
use App\Models\User;
use App\Models\UserAchievementProgress;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AchievementService
{
    public function handleEvent(User $user, string $eventSlug, array $metadata = []): void
    {
        $achievements = Achievement::where('is_active', true)
            ->whereHas('rules', function ($query) use ($eventSlug) {
                $query->where('event', $eventSlug);
            })
            ->with('rules')
            ->get();

        foreach ($achievements as $achievement) {
            $this->evaluateAchievement($user, $achievement, $eventSlug, $metadata);
        }
    }

    protected function evaluateAchievement(User $user, Achievement $achievement, string $eventSlug, array $metadata): void
    {
        // Check if already unlocked and not repeatable
        if (! $achievement->repeatable && $user->achievements()->where('achievement_id', $achievement->id)->exists()) {
            return;
        }

        $progress = $user->achievementProgress()->firstOrCreate(
            ['achievement_id' => $achievement->id],
            ['progress' => 0, 'metadata' => []]
        );

        foreach ($achievement->rules as $rule) {
            if ($rule->event !== $eventSlug) {
                continue;
            }

            $this->applyRule($progress, $rule, $metadata);
        }

        // Check if all rules are satisfied (for simplicity now, we assume one rule or cumulative progress)
        // In a more complex system, we'd check all rules.

        if ($this->isGoalReached($achievement, $progress)) {
            $this->unlockAchievement($user, $achievement);
        }
    }

    protected function applyRule(UserAchievementProgress $progress, $rule, array $metadata): void
    {
        switch ($progress->achievement->type) {
            case 'count':
            case 'category_count':
                $progress->progress += 1;
                break;

            case 'streak':
                $this->handleStreak($progress, $metadata);
                break;

                // Add more types as needed
        }

        $progress->save();
    }

    protected function handleStreak(UserAchievementProgress $progress, array $metadata): void
    {
        $lastUpdate = $progress->updated_at;
        $now = Carbon::now();

        // Grace period until 04:00 AM
        $todayLimit = Carbon::today()->addHours(4);
        $yesterdayLimit = Carbon::yesterday()->addHours(4);

        if ($lastUpdate && $lastUpdate->isAfter($yesterdayLimit) && $lastUpdate->isBefore($todayLimit)) {
            // Already counted today
            return;
        }

        if ($lastUpdate && $lastUpdate->isAfter($yesterdayLimit->subDay())) {
            // Consecutive day
            $progress->progress += 1;
        } else {
            // Streak broken
            $progress->progress = 1;
        }
    }

    protected function isGoalReached(Achievement $achievement, UserAchievementProgress $progress): bool
    {
        $rule = $achievement->rules->first(); // Simplification
        if (! $rule) {
            return false;
        }

        $target = (int) $rule->target;

        switch ($rule->operator) {
            case '>=': return $progress->progress >= $target;
            case '==': return $progress->progress == $target;
            default: return $progress->progress >= $target;
        }
    }

    protected function unlockAchievement(User $user, Achievement $achievement): void
    {
        DB::transaction(function () use ($user, $achievement) {
            $user->achievements()->attach($achievement->id, [
                'completed_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            // Award XP
            $user->increment('xp', $achievement->points);

            // Handle level up logic if needed
            $this->checkLevelUp($user);
        });

        // Trigger notification or event for UI animation
        // event(new AchievementUnlocked($user, $achievement));
    }

    protected function checkLevelUp(User $user): void
    {
        // Simple level logic: level = floor(sqrt(xp / 100)) + 1
        $newLevel = (int) floor(sqrt($user->xp / 100)) + 1;
        if ($newLevel > $user->level) {
            $user->update(['level' => $newLevel]);
            // event(new UserLeveledUp($user, $newLevel));
        }
    }
}
