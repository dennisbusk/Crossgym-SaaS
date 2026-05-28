<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\BookingCreated;
use App\Events\UserCheckedIn;
use App\Events\UserRegistered;
use App\Events\WorkoutLogCreated;
use App\Services\AchievementService;
use App\Services\PRService;
use Illuminate\Contracts\Queue\ShouldQueue;

class AchievementListener implements ShouldQueue
{
    public function __construct(
        protected AchievementService $achievementService,
        protected PRService $prService
    ) {}

    public function handleUserCheckedIn(UserCheckedIn $event): void
    {
        $this->achievementService->handleEvent($event->checkIn->user, 'user.checked_in');
        app(\App\Services\ChallengeService::class)->updateProgress($event->checkIn->user, 'check_ins', 1);
    }

    public function handleWorkoutLogCreated(WorkoutLogCreated $event): void
    {
        $user = $event->workoutLog->user;
        $this->achievementService->handleEvent($user, 'user.completed_workout');

        // Update Challenges
        app(\App\Services\ChallengeService::class)->updateProgress($user, 'workouts_count', 1);
        if ($event->workoutLog->weight && $event->workoutLog->reps && $event->workoutLog->sets) {
            $volume = $event->workoutLog->weight * $event->workoutLog->reps * $event->workoutLog->sets;
            app(\App\Services\ChallengeService::class)->updateProgress($user, 'volume_kg', (float) $volume);
        }

        // Check for PRs
        $prs = $this->prService->evaluatePR($event->workoutLog);
        if (!empty($prs)) {
            // Logik til at gemme PRs eller trigger events
        }
    }

    public function handleBookingCreated(BookingCreated $event): void
    {
        $this->achievementService->handleEvent($event->user, 'user.booked_class');
    }

    public function handleUserRegistered(UserRegistered $event): void
    {
        $this->achievementService->handleEvent($event->user, 'user.registered');
    }

    public function subscribe($events): array
    {
        return [
            UserCheckedIn::class => 'handleUserCheckedIn',
            WorkoutLogCreated::class => 'handleWorkoutLogCreated',
            BookingCreated::class => 'handleBookingCreated',
            UserRegistered::class => 'handleUserRegistered',
        ];
    }
}
