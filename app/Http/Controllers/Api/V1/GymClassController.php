<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\GymClassResource;
use App\Models\GymClass;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Waitlist;
use Illuminate\Http\Request;

class GymClassController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = GymClass::query();

        if ($request->has('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }

        if ($request->has('from_date')) {
            $query->where('class_start', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->where('class_start', '<=', $request->to_date);
        }

        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        $classes = $query->get();

        return GymClassResource::collection($classes);
    }

    public function book(Request $request, GymClass $gymClass)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $gymClass->load(['participants', 'trials']);

        $participantsCount = $gymClass->participants->count() + $gymClass->trials->count();
        $isFull = $participantsCount >= (int) $gymClass->max_participants;
        $userIsBooked = $gymClass->participants->contains($user->id);
        $isPast = $gymClass->class_start && $gymClass->class_start->lt(now());

        if ($userIsBooked) {
            return response()->json(['message' => __('You are already booked for this class')], 422);
        }

        if ($isFull) {
            return response()->json(['message' => __('This class is full')], 422);
        }

        if ($isPast) {
            return response()->json(['message' => __('This class has already started')], 422);
        }

        // Logic for checking subscription/credits
        $sub = Subscription::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('user_id', $user->id)
            ->first();

        if (! $sub || ($sub->ends_at && $sub->ends_at->isPast())) {
            return response()->json(['message' => __('You need an active subscription to book a class')], 403);
        }

        $booking = \DB::transaction(function () use ($user, $gymClass, $sub) {
            if ($sub) {
                $planType = (string) ($sub->plan_type ?? 'subscription');
                if ($planType === 'one_off') {
                    if ($sub->isDayPass()) {
                        $otherBookingsToday = $user->attendingClasses()
                            ->whereDate('class_start', $gymClass->class_start->toDateString())
                            ->where('classes.id', '!=', $gymClass->id)
                            ->count();

                        if ($otherBookingsToday === 0) {
                            if ($sub->credits_remaining < 1) {
                                throw new \Exception(__('You have no credits left'));
                            }
                            $sub->decrement('credits_remaining', 1.0);
                        } elseif ($otherBookingsToday === 1) {
                            if ($sub->credits_remaining < 0.5) {
                                throw new \Exception(__('You have no credits left'));
                            }
                            $sub->decrement('credits_remaining', 0.5);
                        }
                    } else {
                        if ($sub->credits_remaining <= 0) {
                            throw new \Exception(__('You have no credits left'));
                        }
                        $sub->decrement('credits_remaining');
                    }
                }
            }

            $checkInId = null;
            if ($sub && $sub->plan_type === 'one_off') {
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
                    'gym_class_id' => $gymClass->id,
                ]);
                $checkInId = $checkIn->id;
            }

            $gymClass->participants()->syncWithoutDetaching([$user->id => ['check_in_id' => $checkInId]]);

            event(new \App\Events\BookingCreated($user, $gymClass));

            return [
                'id' => $gymClass->id, // Or a real booking ID if there was a pivot model
                'gym_class_id' => $gymClass->id,
                'status' => 'confirmed',
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => __('Spot reserved successfully'),
            'booking' => $booking,
        ]);
    }

    public function cancelBooking(Request $request, GymClass $gymClass)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        if (! $gymClass->participants->contains($user->id)) {
            return response()->json(['message' => __('You are not booked for this class')], 422);
        }

        $gymClass->participants()->detach($user->id);

        return response()->json([
            'status' => 'success',
            'message' => __('Booking cancelled'),
        ]);
    }

    public function joinWaitlist(Request $request, GymClass $gymClass)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $gymClass->load(['participants', 'trials', 'waitlist']);

        $participantsCount = $gymClass->participants->count() + $gymClass->trials->count();
        $isFull = $participantsCount >= (int) $gymClass->max_participants;

        if (! $isFull) {
            return response()->json(['message' => __('This class is not full yet, please book instead')], 422);
        }

        if ($gymClass->participants->contains($user->id)) {
            return response()->json(['message' => __('You are already booked for this class')], 422);
        }

        if ($gymClass->waitlist->contains($user->id)) {
            return response()->json(['message' => __('You are already on the waitlist')], 422);
        }

        $waitlist = Waitlist::create([
            'gym_class_id' => $gymClass->id,
            'user_id' => $user->id,
        ]);

        $position = $gymClass->waitlist()->count();

        return response()->json([
            'id' => $waitlist->id,
            'position' => $position,
            'estimated_chance' => $position <= 3 ? 'high' : ($position <= 10 ? 'medium' : 'low'),
        ]);
    }

    public function wod(Request $request, GymClass $gymClass)
    {
        // Simple WOD reveal logic
        return response()->json([
            'title' => $gymClass->getTranslation('name', 'da'),
            'type' => 'AMRAP 20',
            'description' => $gymClass->getTranslation('description', 'da') ?: '6 Burpees, 9 Pull-ups, 12 Air Squats',
            'equipment' => ['Pull-up bar'],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'name' => 'required|array',
            'description' => 'nullable|array',
            'trainer_id' => 'nullable|exists:users,id',
            'class_type_id' => 'required|exists:class_types,id',
            'max_participants' => 'required|integer|min:1',
            'class_start' => 'required|date',
            'class_end' => 'required|date|after:class_start',
            'all_day_event' => 'boolean',
            'featured' => 'boolean',
        ]);

        $class = GymClass::create($validated);

        return new GymClassResource($class);
    }

    /**
     * Display the specified resource.
     */
    public function show(GymClass $gymClass)
    {
        return new GymClassResource($gymClass);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GymClass $gymClass)
    {
        $validated = $request->validate([
            'name' => 'sometimes|array',
            'description' => 'sometimes|array',
            'trainer_id' => 'sometimes|nullable|exists:users,id',
            'class_type_id' => 'sometimes|exists:class_types,id',
            'max_participants' => 'sometimes|integer|min:1',
            'class_start' => 'sometimes|date',
            'class_end' => 'sometimes|date|after:class_start',
            'all_day_event' => 'sometimes|boolean',
            'featured' => 'sometimes|boolean',
        ]);

        $gymClass->update($validated);

        return new GymClassResource($gymClass);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GymClass $gymClass)
    {
        $gymClass->delete();

        return response()->json(null, 204);
    }
}
