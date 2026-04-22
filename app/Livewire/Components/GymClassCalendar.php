<?php

namespace App\Livewire\Components;

use App\Models\GymClass;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Traits\ComponentRelationshipCache;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;

class GymClassCalendar extends Component
{
    use ComponentRelationshipCache;

    #[On('loadEvents')]
    public function loadEvents($start = null, $end = null): void
    {
        $start = Carbon::parse($start ?? now()->toDateString())->startOfMonth()->startOfWeek();
        $end = Carbon::parse($end ?? now()->toDateString())->endOfMonth()->endOfWeek();
        // Fetch events for this range
        $events = $this->getEvents($start, $end);
        // Dispatch back to JS
        $this->dispatch('events-updated', events: $events);
    }

    public function render()
    {
        $start = Carbon::parse(now()->toDateString())->startOfMonth()->startOfWeek();
        $end = Carbon::parse(now()->toDateString())->endOfMonth()->endOfWeek();
        $events = $this->getEvents($start, $end);

        return view('livewire.components.gym-class-calendar')->with(['events' => $events]);
    }

    public function getEvents($start, $end)
    {
        /** @var User|null $user */
        $user = auth()->user();
        $tenantId = $user?->tenant_id;

        // Precompute subscription/plan context for the logged-in user to avoid repeating work per event
        $weeklyLimit = 0;
        $usedThisWeek = 0;
        $creditsRemaining = null; // null means not applicable

        if ($user && $user->tenant) {
            $sub = Subscription::query()
                ->where('tenant_id', $user->tenant_id)
                ->where('user_id', $user->id)
                ->first();

            if ($sub) {
                $plan = $sub->stripe_price_id ? Plan::query()->where('stripe_price_id', $sub->stripe_price_id)->first() : null;
                $metadata = (array) ($plan?->metadata ?? []);
                $planType = (string) ($sub->plan_type ?: ($metadata['plan_type'] ?? 'subscription'));

                // Weekly booking limit (for subscriptions)
                $weeklyLimit = isset($metadata['weekly_booking_limit']) ? (int) $metadata['weekly_booking_limit'] : 0;
                if ($weeklyLimit > 0) {
                    $weekStart = now()->startOfWeek();
                    $weekEnd = now()->endOfWeek();
                    $usedThisWeek = $user->attendingClasses()
                        ->whereBetween('class_start', [$weekStart, $weekEnd])
                        ->count();
                }

                if ($planType === 'one_off') {
                    $creditsRemaining = (int) ($sub->credits_remaining ?? 0);
                }
            }
        }

        $query = GymClass::query()
            ->with(['classType', 'trainer', 'participants', 'trials', 'color'])
            ->whereBetween('class_start', [$start, $end]);

        // Scope classes to the user's tenant when logged in
        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        // Note: seat release for unchecked participants is now handled by a scheduled job
        // (ReleaseUncheckedSeatsJob). We intentionally avoid doing it on-demand here to scale
        // better and reduce unexpected side effects on reads.

        return $query
            ->get()
            ->map(function (GymClass $event) use ($user, $weeklyLimit, $usedThisWeek, $creditsRemaining) {
                $regularParticipantsCount = $event->participants->count();
                $trialsCount = $event->trials->count();
                $participantsCount = $regularParticipantsCount + $trialsCount;

                $isFull = $participantsCount >= (int) $event->max_participants;
                $userIsBooked = $user ? $event->participants->contains($user->id) : false;
                $now = now();
                $isPast = $event->class_end && $event->class_end->lt($now);
                $hasStarted = $event->class_start && $event->class_start->lte($now);
                $checkInWindowOpen = $event->class_start && $now->between($event->class_start->copy()->subMinutes(30), $event->class_start);

                $isTrainer = $user && $event->trainer_id === $user->id;
                $isAdmin = $user && $user->hasPermission('GymClass', 'update');
                $canManage = $isTrainer || $isAdmin;

                [$canBook, $reason] = $this->canUserBook($event, $user, $userIsBooked, $isFull, $isPast);

                $color = $event->color?->color ?: '#3b82f6';

                return [
                    'id' => $event->id,
                    'title' => $event->name,
                    'start' => optional($event->class_start)->toIso8601String(),
                    'end' => optional($event->class_end)->toIso8601String(),
                    'allDay' => (bool) $event->all_day_event,
                    'display' => 'block',
                    'color' => $color,
                    'backgroundColor' => $color,
                    'borderColor' => $color,
                    'textColor' => 'black',
                    'extendedProps' => [
                        'trainer' => $event->trainer?->name,
                        'maxParticipants' => $event->max_participants,
                        'participantsCount' => $participantsCount,
                        'availableSeats' => max(0, ((int) $event->max_participants) - $participantsCount),
                        'userIsBooked' => $userIsBooked,
                        'canBook' => $canBook,
                        'cannotBookReason' => $canBook ? null : $reason,
                        'participants' => array_merge(
                            $event->participants->map(fn (User $u) => [
                                'id' => $u->id,
                                'name' => $u->name,
                                'type' => 'member',
                                'checkedIn' => ! is_null($u->pivot?->checked_at),
                                'checkedInAt' => optional($u->pivot?->checked_at)->toDateTimeString(),
                            ])->values()->all(),
                            $event->trials->map(fn ($t) => [
                                'id' => 'trial-'.$t->id,
                                'name' => $t->name.' ('.__('Trial').')',
                                'type' => 'trial',
                                'checkedIn' => ! is_null($t->checked_at),
                                'checkedInAt' => optional($t->checked_at)->toDateTimeString(),
                            ])->values()->all()
                        ),
                        'canManage' => $canManage,
                        'checkInWindowOpen' => (bool) $checkInWindowOpen,
                        'hasStarted' => (bool) $hasStarted,
                        // Subscription limitation context for UI
                        'weeklyLimit' => $weeklyLimit,
                        'usedThisWeek' => $usedThisWeek,
                        'creditsRemaining' => $creditsRemaining,
                    ],
                ];
            });
    }

    /**
     * Determine if the current user can book the given class.
     */
    protected function canUserBook(GymClass $event, ?User $user, bool $userIsBooked, bool $isFull, bool $isPast): array
    {
        if (! $user) {
            return [false, __('You must be logged in to book')];
        }

        // Must belong to a tenant
        $tenant = $user->tenant;
        if (! $tenant) {
            return [false, __('Your tenant does not have an active subscription')];
        }

        if ($userIsBooked) {
            return [false, __('You have already booked this class')];
        }

        if ($isFull) {
            return [false, __('This class is full')];
        }

        if ($isPast) {
            return [false, __('This class has already ended')];
        }

        // Load user's subscription row
        $sub = $user->subscription;

        if (! $sub || $sub->status !== 'active') {
            return [false, __('You do not have an active subscription')];
        }

        $plan = $sub->plan ?? null;
        $metadata = (array) ($plan?->metadata ?? []);
        $planType = (string) ($sub->plan_type ?: ($metadata['plan_type'] ?? 'subscription'));

        // Enforce allowed class types if configured
        $allowedClassTypes = json_decode((string) ($metadata['allowed_class_type_ids'] ?? '[]'), true) ?? [];
        if (! empty($allowedClassTypes)) {
            if (! in_array((int) $event->class_type_id, array_map('intval', $allowedClassTypes), true)) {
                return [false, __('Your plan does not allow booking this class type')];
            }
        }

        // Determine eligibility based on plan type
        if ($planType === 'subscription') {
            // status must be trialing or active
            $status = (string) ($sub->status ?? '');
            if (! in_array($status, ['trialing', 'active'], true)) {
                return [false, __('Your subscription is not active')];
            }
            // Weekly limit check
            $weeklyLimit = isset($metadata['weekly_booking_limit']) ? (int) $metadata['weekly_booking_limit'] : 0;
            if ($weeklyLimit > 0) {
                $weekStart = now()->startOfWeek();
                $weekEnd = now()->endOfWeek();
                $countThisWeek = $user->attendingClasses()
                    ->whereBetween('class_start', [$weekStart, $weekEnd])
                    ->count();
                if ($countThisWeek >= $weeklyLimit) {
                    return [false, __('You have reached your weekly booking limit')];
                }
            }

            return [true, null];
        }

        // one_off credits
        if ($planType === 'one_off') {
            $credits = (int) ($sub->credits_remaining ?? 0);
            if ($credits <= 0) {
                return [false, __('You have no credits left')];
            }

            return [true, null];
        }

        return [false, __('Your tenant does not have an active subscription')];
    }

    public function book(int $classId): void
    {
        /** @var User|null $user */
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $event = GymClass::query()->with(['participants', 'trials'])->findOrFail($classId);
        $this->authorize('view', $event);
        $participantsCount = $event->participants->count() + $event->trials->count();
        $isFull = $participantsCount >= (int) $event->max_participants;
        $userIsBooked = $event->participants->contains($user->id);
        $isPast = $event->class_end && $event->class_end->lt(now());

        [$canBook] = $this->canUserBook($event, $user, $userIsBooked, $isFull, $isPast);
        if (! $canBook) {
            return;
        }

        // If one_off, decrement a credit atomically
        \DB::transaction(function () use ($user, $event) {
            $sub = Subscription::query()
                ->where('tenant_id', $user->tenant_id)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if ($sub) {
                $planType = (string) ($sub->plan_type ?? 'subscription');
                if ($planType === 'one_off') {
                    // Check for Day Pass (Dagskort) logic:
                    // If they have 1 or more bookings already today, it's effectively a "Full Day Pass" (Heldagskort)
                    // and subsequent bookings are free.
                    if ($sub->isDayPass()) {
                        $otherBookingsToday = $user->attendingClasses()
                            ->whereDate('class_start', $event->class_start->toDateString())
                            ->where('classes.id', '!=', $event->id)
                            ->count();

                        if ($otherBookingsToday === 0) {
                            // First booking of the day: full price (1 credit)
                            $credits = (float) ($sub->credits_remaining ?? 0);
                            if ($credits < 1) {
                                return;
                            }
                            $sub->decrement('credits_remaining', 1.0);
                        } elseif ($otherBookingsToday === 1) {
                            // Second booking of the day: half price (0.5 credit)
                            $credits = (float) ($sub->credits_remaining ?? 0);
                            if ($credits < 0.5) {
                                return;
                            }
                            $sub->decrement('credits_remaining', 0.5);
                        }
                        // Third and subsequent bookings: free (0 credits)
                    } else {
                        // Regular one_off credits
                        $credits = (int) ($sub->credits_remaining ?? 0);
                        if ($credits <= 0) {
                            return;
                        }
                        $sub->decrement('credits_remaining');
                    }
                }
            }

            // Attach if not already booked
            $checkInId = null;
            if ($sub && $sub->plan_type === 'one_off') {
                // Get latest payment for this user to link it to the VisitLog
                $latestPayment = Payment::query()
                    ->where('user_id', $user->id)
                    ->where('status', 'succeeded')
                    ->latest()
                    ->first();

                $checkIn = \App\Models\CheckIn::create([
                    'tenant_id' => $user->tenant_id,
                    'user_id' => $user->id,
                    'is_paid' => true,
                    'charge_id' => $latestPayment?->stripe_payment_intent_id,
                    'gym_class_id' => $event->id,
                ]);
                $checkInId = $checkIn->id;
            }

            $event->participants()->syncWithoutDetaching([$user->id => ['check_in_id' => $checkInId]]);

            event(new \App\Events\BookingCreated($user, $event));
        });

        // Notify and refresh calendar events in current range
        session()->flash('success', __('Class booked successfully'));
        $this->loadEvents();
    }

    public function cancelBooking(int $classId): void
    {
        /** @var User|null $user */
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $event = GymClass::query()->with('participants')->findOrFail($classId);
        $this->authorize('view', $event);

        \DB::transaction(function () use ($user, $event) {
            if ($event->participants->contains($user->id)) {
                $participant = $event->participants()->where('user_id', $user->id)->first();
                $isCheckedIn = $participant && $participant->pivot->checkIn && $participant->pivot->checkIn->checked_at !== null;

                $event->participants()->detach($user->id);

                if ($event->class_start && $event->class_start->isFuture()) {
                    $sub = Subscription::query()
                        ->where('tenant_id', $user->tenant_id)
                        ->where('user_id', $user->id)
                        ->lockForUpdate()
                        ->first();

                    if ($sub && $sub->plan_type === 'one_off') {
                        // Refund logic for one_off/Dagskort:
                        // Refund only if more than 5 minutes before start OR if already checked in.
                        $refundableUntil = $event->class_start->copy()->subMinutes(5);
                        if ($isCheckedIn || now()->lte($refundableUntil)) {
                            if ($sub->isDayPass()) {
                                $otherBookingsThatDay = $user->attendingClasses()
                                    ->whereDate('class_start', $event->class_start->toDateString())
                                    ->where('classes.id', '!=', $event->id)
                                    ->count();

                                if ($otherBookingsThatDay === 0) {
                                    $sub->increment('credits_remaining', 1.0);
                                } elseif ($otherBookingsThatDay === 1) {
                                    $sub->increment('credits_remaining', 0.5);
                                }
                            } else {
                                $sub->increment('credits_remaining');
                            }
                        }
                    }
                }
            }
        });

        // Notify and refresh calendar events
        session()->flash('success', __('Booking cancelled'));
        $this->loadEvents();
    }

    /**
     * Allow a participant to self check-in within 30 minutes before start until class start.
     * Optionally validates location if the tenant has coordinates set.
     */
    public function selfCheckIn(int $classId, ?float $latitude = null, ?float $longitude = null): void
    {
        /** @var User|null $user */
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $event = GymClass::query()->with(['participants' => function ($q) use ($user) {
            $q->where('users.id', $user->id);
        }])->findOrFail($classId);

        $participant = $event->participants->first();
        if (! $participant) {
            return;
        } // ikke booket

        $now = now();
        if (! $event->class_start) {
            return;
        }
        if (! $now->between($event->class_start->copy()->subMinutes(30), $event->class_start)) {
            session()->flash('error', __('You can only check in between 30 minutes before and the start of the class.'));

            return;
        } // uden for tidsrum

        // Lokationsvalidering (Geofencing)
        $tenant = $user->tenant;
        if ($tenant && $tenant->latitude && $tenant->longitude && $tenant->checkin_radius > 0) {
            if ($latitude === null || $longitude === null) {
                session()->flash('error', __('Location access is required to check in.'));

                return;
            }

            $distance = $this->calculateDistance(
                (float) $latitude,
                (float) $longitude,
                (float) $tenant->latitude,
                (float) $tenant->longitude
            );

            if ($distance > (int) $tenant->checkin_radius) {
                session()->flash('error', __('You are too far from the club to check in.'));

                return;
            }
        }

        // Update or create VisitLog
        $checkInId = $participant->pivot->check_in_id;
        if ($checkInId) {
            $checkIn = \App\Models\CheckIn::find($checkInId);
            if ($checkIn) {
                $checkIn->update(['checked_at' => $now]);
            }
        } else {
            $checkIn = \App\Models\CheckIn::create([
                'tenant_id' => $user->tenant_id,
                'user_id' => $user->id,
                'gym_class_id' => $event->id,
                'checked_at' => $now,
            ]);
            $event->participants()->updateExistingPivot($user->id, ['check_in_id' => $checkIn->id]);
        }

        $user->update(['last_check_in_at' => $now]);

        session()->flash('success', __('Checked in successfully'));
        $this->loadEvents();
    }

    /**
     * Calculate distance between two points in meters using Haversine formula.
     */
    protected function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000; // meter

        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lngDelta / 2) * sin($lngDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Trainer/Admin: check in a participant.
     */
    public function checkInParticipant(int $classId, int $userId): void
    {
        /** @var User|null $actor */
        $actor = auth()->user();
        if (! $actor) {
            return;
        }

        $event = GymClass::query()->with(['participants' => function ($q) use ($userId) {
            $q->where('users.id', $userId);
        }, 'trainer'])->findOrFail($classId);
        $this->authorize('update', $event);

        $participant = $event->participants->first();
        if (! $participant) {
            return;
        }

        $now = now();
        $checkInId = $participant->pivot->check_in_id;

        if ($checkInId) {
            $checkIn = \App\Models\CheckIn::find($checkInId);
            if ($checkIn) {
                $checkIn->update(['checked_at' => $now]);
            }
        } else {
            $checkIn = \App\Models\CheckIn::create([
                'tenant_id' => $event->tenant_id,
                'user_id' => $userId,
                'gym_class_id' => $event->id,
                'checked_at' => $now,
            ]);
            $event->participants()->updateExistingPivot($userId, ['check_in_id' => $checkIn->id]);
        }

        // Update user's last check-in
        User::query()->where('id', $userId)->update(['last_check_in_at' => $now]);

        session()->flash('success', __('Participant checked in'));
        $this->loadEvents();
    }

    /**
     * Trainer/Admin: remove a participant's booking (cancel).
     */
    public function removeParticipant(int $classId, int $userId): void
    {
        /** @var User|null $actor */
        $actor = auth()->user();
        if (! $actor) {
            return;
        }

        $event = GymClass::query()->with('participants', 'trainer')->findOrFail($classId);
        $canManage = ($actor->id === $event->trainer_id) || $actor->hasPermission('GymClass', 'update');
        if (! $canManage) {
            return;
        }

        if ($event->participants->contains($userId)) {
            $event->participants()->detach($userId);
        }

        session()->flash('success', __('Booking removed'));
        $this->loadEvents();
    }

    /**
     * Trainer/Admin: add a participant by email.
     */
    public function addParticipantByEmail(int $classId, string $email): void
    {
        /** @var User|null $actor */
        $actor = auth()->user();
        if (! $actor) {
            return;
        }

        $event = GymClass::query()->with(['participants', 'trials', 'trainer'])->findOrFail($classId);
        $canManage = ($actor->id === $event->trainer_id) || $actor->hasPermission('GymClass', 'update');
        if (! $canManage) {
            return;
        }

        $user = User::query()->where('email', $email)->first();
        if (! $user) {
            session()->flash('error', __('User not found'));

            return;
        }
        // tenant check: only allow same tenant
        if ($user->tenant_id !== $actor->tenant_id || $user->tenant_id !== $event->tenant_id) {
            session()->flash('error', __('User cannot be added to this class'));

            return;
        }
        // capacity check
        $participantsCount = $event->participants->count() + $event->trials->count();
        if ($participantsCount >= (int) $event->max_participants) {
            session()->flash('error', __('This class is full'));

            return;
        }
        $event->participants()->syncWithoutDetaching([$user->id]);
        session()->flash('success', __('Participant added'));
        $this->loadEvents();
    }
}
