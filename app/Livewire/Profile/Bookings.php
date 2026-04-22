<?php

declare(strict_types=1);

namespace App\Livewire\Profile;

use App\Models\GymClass;
use App\Models\Subscription;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

// #[Layout('components.layouts.auth.sidebar')]
class Bookings extends Component
{
    public array $upcoming = [];

    public array $past = [];

    public bool $showModal = false;

    public array $selected = [];

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        // Fetch upcoming and past bookings for the current user
        $now = now();

        $this->upcoming = $user->attendingClasses()
            ->where('classes.class_start', '>=', $now)
            ->orderBy('classes.class_start')
            ->get([
                'classes.id as id',
                'classes.name as name',
                'classes.class_start as class_start',
                'classes.class_end as class_end',
            ])
            ->map(function (GymClass $c) {
                return [
                    'id' => $c->id,
                    'name' => (string) ($c->hasTranslation('name') ? $c->getTranslation('name', app()->getLocale(), true) : $c->name),
                    'start' => (string) $c->class_start,
                    'end' => (string) $c->class_end,
                ];
            })->all();

        $this->past = $user->attendingClasses()
            ->where('classes.class_start', '<', $now)
            ->orderByDesc('classes.class_start')
            ->limit(20)
            ->get([
                'classes.id as id',
                'classes.name as name',
                'classes.class_start as class_start',
                'classes.class_end as class_end',
            ])
            ->map(function (GymClass $c) {
                return [
                    'id' => $c->id,
                    'name' => (string) ($c->hasTranslation('name') ? $c->getTranslation('name', app()->getLocale(), true) : $c->name),
                    'start' => (string) $c->class_start,
                    'end' => (string) $c->class_end,
                ];
            })->all();
    }

    public function showBooking(int $id): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        /** @var GymClass $event */
        $event = GymClass::query()
            ->with(['trainer', 'participants', 'trials'])
            ->whereKey($id)
            ->firstOrFail();

        // Subscription/plan context
        $weeklyLimit = 0;
        $usedThisWeek = 0;
        $creditsRemaining = null; // null => not applicable

        $sub = $user->subscription;
        if ($sub) {
            $plan = $sub->plan;
            $metadata = (array) ($plan?->metadata ?? []);
            $planType = (string) ($sub->plan_type ?: ($metadata['plan_type'] ?? 'subscription'));

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

        $participantsCount = $event->participants->count() + $event->trials->count();
        $maxParticipants = (int) $event->max_participants;
        $isPast = $event->class_end && $event->class_end->lt(now());

        $this->selected = [
            'id' => $event->id,
            'title' => (string) ($event->hasTranslation('name') ? $event->getTranslation('name', app()->getLocale(), true) : $event->name),
            'start' => optional($event->class_start)->toIso8601String(),
            'end' => optional($event->class_end)->toIso8601String(),
            'trainer' => $event->trainer?->name,
            'participantsCount' => $participantsCount,
            'maxParticipants' => $maxParticipants,
            'weeklyLimit' => $weeklyLimit,
            'usedThisWeek' => $usedThisWeek,
            'creditsRemaining' => $creditsRemaining,
            'isPast' => $isPast,
        ];

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->selected = [];
    }

    public function cancelBooking(int $classId): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $event = GymClass::query()->with('participants')->findOrFail($classId);
        $this->authorize('view', $event);

        DB::transaction(function () use ($user, $event) {
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
                                // For Dagskort, refund depends on which booking was cancelled.
                                // If they cancelled the 1st booking, they should get 1 back.
                                // If they cancelled the 2nd booking, they should get 0.5 back.
                                // But it's tricky because if they had 3 bookings and cancel the 1st,
                                // the "remaining" ones shift their "order".

                                // Simple logic: if they cancel a booking on a day where they have X OTHER bookings:
                                $otherBookingsThatDay = $user->attendingClasses()
                                    ->whereDate('class_start', $event->class_start->toDateString())
                                    ->where('classes.id', '!=', $event->id)
                                    ->count();

                                if ($otherBookingsThatDay === 0) {
                                    // It was the ONLY booking that day. Refund 1.
                                    $sub->increment('credits_remaining', 1.0);
                                } elseif ($otherBookingsThatDay === 1) {
                                    // There was 1 other booking.
                                    // This means one was 1.0 and one was 0.5.
                                    // Total spent was 1.5. If they have 1 left, that 1 should now cost 1.0.
                                    // So they spent 1.5, should have spent 1.0. Refund 0.5.
                                    $sub->increment('credits_remaining', 0.5);
                                }
                                // If they had 2 or more other bookings, those were free anyway. Refund 0.
                            } else {
                                $sub->increment('credits_remaining');
                            }
                        }
                    }
                }
            }
        });

        // Refresh lists
        $this->mount();
        session()->flash('success', __('Booking cancelled'));
        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.profile.bookings');
    }
}
