<?php

declare(strict_types=1);

use App\Livewire\Profile\Bookings;
use App\Models\ClassType;
use App\Models\GymClass;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->role = \App\Models\Role::query()->firstOrCreate(['slug' => 'member'], ['name' => 'Member']);
    $this->user = User::factory()->create([
        'tenant_id' => $this->tenant->id,
        'role_id' => $this->role->id,
    ]);
    $this->classType = ClassType::factory()->create(['tenant_id' => $this->tenant->id]);

    // Create a regular one_off plan
    $this->plan = Plan::create([
        'tenant_id' => $this->tenant->id,
        'name' => 'Klippekort',
        'stripe_price_id' => 'price_klippekort',
        'amount' => 10000, // 100 DKK
        'currency' => 'DKK',
        'metadata' => ['plan_type' => 'one_off'],
    ]);

    $this->sub = Subscription::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->user->id,
        'stripe_subscription_id' => 'sub_test_123',
        'stripe_price_id' => $this->plan->stripe_price_id,
        'plan_type' => 'one_off',
        'status' => 'active',
        'credits_remaining' => 2,
    ]);

    $this->permission = \App\Models\Permission::query()->firstOrCreate(
        ['model' => 'GymClass', 'ability' => 'view'],
        ['name' => 'View GymClass']
    );
    $this->user->permissions()->syncWithoutDetaching([$this->permission->id => ['granted' => true]]);
});

it('denies refund if cancelled less than 5 minutes before start for one_off plan', function () {
    $class = GymClass::create([
        'tenant_id' => $this->tenant->id,
        'name' => ['da' => 'Test Class'],
        'description' => ['da' => ''],
        'trainer_id' => $this->user->id,
        'class_type_id' => $this->classType->id,
        'max_participants' => 10,
        'class_start' => now()->addMinutes(4), // Less than 5 mins
        'class_end' => now()->addHour(),
    ]);
    $class->participants()->attach($this->user->id);
    $this->sub->update(['credits_remaining' => 1]);

    $this->actingAs($this->user);

    Livewire::test(Bookings::class)
        ->call('cancelBooking', $class->id);

    $this->sub->refresh();
    expect((int) $this->sub->credits_remaining)->toBe(1); // No refund
});

it('allows refund if cancelled more than 5 minutes before start for one_off plan', function () {
    $class = GymClass::create([
        'tenant_id' => $this->tenant->id,
        'name' => ['da' => 'Test Class'],
        'description' => ['da' => ''],
        'trainer_id' => $this->user->id,
        'class_type_id' => $this->classType->id,
        'max_participants' => 10,
        'class_start' => now()->addMinutes(10), // More than 5 mins
        'class_end' => now()->addHour(),
    ]);
    $class->participants()->attach($this->user->id);
    $this->sub->update(['credits_remaining' => 1]);

    $this->actingAs($this->user);

    Livewire::test(Bookings::class)
        ->call('cancelBooking', $class->id);

    $this->sub->refresh();
    expect((int) $this->sub->credits_remaining)->toBe(2); // Refunded
});

it('allows refund if already checked in even if less than 5 minutes before start', function () {
    $class = GymClass::create([
        'tenant_id' => $this->tenant->id,
        'name' => ['da' => 'Test Class'],
        'description' => ['da' => ''],
        'trainer_id' => $this->user->id,
        'class_type_id' => $this->classType->id,
        'max_participants' => 10,
        'class_start' => now()->addMinutes(2),
        'class_end' => now()->addHour(),
    ]);
    $checkIn = \App\Models\CheckIn::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->user->id,
        'gym_class_id' => $class->id,
        'checked_at' => now(),
    ]);
    $class->participants()->attach($this->user->id, ['check_in_id' => $checkIn->id]);
    $this->sub->update(['credits_remaining' => 1]);

    $this->actingAs($this->user);

    Livewire::test(Bookings::class)
        ->call('cancelBooking', $class->id);

    $this->sub->refresh();
    expect((int) $this->sub->credits_remaining)->toBe(2); // Refunded because isCheckedIn
});

it('upgrades Dagskort correctly (second is half price, third is free)', function () {
    // Change plan to Dagskort
    $this->plan->update(['name' => 'Dagskort', 'metadata' => ['plan_type' => 'one_off', 'is_day_pass' => true]]);
    $this->sub->update(['credits_remaining' => 5]);

    $class1 = GymClass::create([
        'tenant_id' => $this->tenant->id,
        'name' => ['da' => 'Morning Class'],
        'description' => ['da' => ''],
        'trainer_id' => $this->user->id,
        'class_type_id' => $this->classType->id,
        'max_participants' => 10,
        'class_start' => now()->addDay()->setTime(10, 0),
        'class_end' => now()->addDay()->setTime(11, 0),
    ]);

    $class2 = GymClass::create([
        'tenant_id' => $this->tenant->id,
        'name' => ['da' => 'Evening Class'],
        'description' => ['da' => ''],
        'trainer_id' => $this->user->id,
        'class_type_id' => $this->classType->id,
        'max_participants' => 10,
        'class_start' => now()->addDay()->setTime(18, 0),
        'class_end' => now()->addDay()->setTime(19, 0),
    ]);

    $class3 = GymClass::create([
        'tenant_id' => $this->tenant->id,
        'name' => ['da' => 'Night Class'],
        'description' => ['da' => ''],
        'trainer_id' => $this->user->id,
        'class_type_id' => $this->classType->id,
        'max_participants' => 10,
        'class_start' => now()->addDay()->setTime(21, 0),
        'class_end' => now()->addDay()->setTime(22, 0),
    ]);

    $this->actingAs($this->user);

    // Book first class
    Livewire::test(\App\Livewire\Components\GymClassCalendar::class)
        ->call('book', $class1->id);

    $this->sub->refresh();
    expect((float) $this->sub->credits_remaining)->toEqual(4.0); // One credit used

    // Book second class same day
    Livewire::test(\App\Livewire\Components\GymClassCalendar::class)
        ->call('book', $class2->id);

    $this->sub->refresh();
    expect((float) $this->sub->credits_remaining)->toEqual(3.5); // Half credit used (4.0 -> 3.5)

    // Book third class same day
    Livewire::test(\App\Livewire\Components\GymClassCalendar::class)
        ->call('book', $class3->id);

    $this->sub->refresh();
    expect((float) $this->sub->credits_remaining)->toEqual(3.5); // Third one is free
    expect($class3->participants()->whereKey($this->user->id)->exists())->toBeTrue();
});

it('refunds correctly for Dagskort when cancelling', function () {
    $this->plan->update(['name' => 'Dagskort', 'metadata' => ['plan_type' => 'one_off', 'is_day_pass' => true]]);
    $this->sub->update(['credits_remaining' => 10]);

    $day = now()->addDays(2);
    $class1 = GymClass::create([
        'tenant_id' => $this->tenant->id, 'name' => ['da' => 'C1'], 'description' => ['da' => ''],
        'trainer_id' => $this->user->id, 'class_type_id' => $this->classType->id, 'max_participants' => 10,
        'class_start' => $day->copy()->setTime(10, 0), 'class_end' => $day->copy()->setTime(11, 0),
    ]);
    $class2 = GymClass::create([
        'tenant_id' => $this->tenant->id, 'name' => ['da' => 'C2'], 'description' => ['da' => ''],
        'trainer_id' => $this->user->id, 'class_type_id' => $this->classType->id, 'max_participants' => 10,
        'class_start' => $day->copy()->setTime(12, 0), 'class_end' => $day->copy()->setTime(13, 0),
    ]);

    $this->actingAs($this->user);

    // Book two classes
    Livewire::test(\App\Livewire\Components\GymClassCalendar::class)->call('book', $class1->id);
    Livewire::test(\App\Livewire\Components\GymClassCalendar::class)->call('book', $class2->id);

    $this->sub->refresh();
    expect((float) $this->sub->credits_remaining)->toEqual(8.5); // 10 - 1.0 - 0.5

    // Cancel the second one (0.5 refund)
    Livewire::test(Bookings::class)->call('cancelBooking', $class2->id);
    $this->sub->refresh();
    expect((float) $this->sub->credits_remaining)->toEqual(9.0); // 8.5 + 0.5

    // Re-book it (should cost 0.5 again because it is the 2nd booking)
    Livewire::test(\App\Livewire\Components\GymClassCalendar::class)->call('book', $class2->id);
    $this->sub->refresh();
    expect((float) $this->sub->credits_remaining)->toEqual(8.5);

    // Cancel the first one (should refund 0.5 because there is 1 other booking left)
    // Actually, wait. If they have 2 bookings and cancel the "1st", they should have 1 booking left which costs 1.0.
    // They spent 1.5. If they cancel 1, they should have spent 1.0. So refund 0.5.
    Livewire::test(Bookings::class)->call('cancelBooking', $class1->id);
    $this->sub->refresh();
    expect((float) $this->sub->credits_remaining)->toEqual(9.0); // 8.5 + 0.5 = 9.0

    // Cancel the last one (refund 1.0)
    Livewire::test(Bookings::class)->call('cancelBooking', $class2->id);
    $this->sub->refresh();
    expect((float) $this->sub->credits_remaining)->toEqual(10.0); // 9.0 + 1.0 = 10.0
});
