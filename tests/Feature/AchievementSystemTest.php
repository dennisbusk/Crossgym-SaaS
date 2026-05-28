<?php

use App\Events\UserCheckedIn;
use App\Models\Achievement;
use App\Models\CheckIn;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
});

it('unlocks a count based achievement when target is reached', function () {
    // Create achievement
    $achievement = Achievement::create([
        'tenant_id' => $this->tenant->id,
        'slug' => 'first_checkin',
        'name' => ['da' => 'Første check-in', 'en' => 'First check-in'],
        'type' => 'count',
        'points' => 100,
        'is_active' => true,
    ]);

    $achievement->rules()->create([
        'event' => 'user.checked_in',
        'operator' => '>=',
        'target' => 1,
    ]);

    // Perform action
    $checkIn = CheckIn::create([
        'user_id' => $this->user->id,
        'tenant_id' => $this->tenant->id,
        'checked_at' => now(),
    ]);

    // Dispatch event manually since we want to test the listener/service
    // Usually, the model would do this, and we already set that up in CheckIn model.
    event(new UserCheckedIn($checkIn));

    // Verify
    expect($this->user->refresh()->achievements)->toHaveCount(1)
        ->and($this->user->xp)->toBe(100)
        ->and($this->user->level)->toBe(2); // floor(sqrt(100/100)) + 1 = 2
});

it('tracks streak achievements correctly', function () {
    $achievement = Achievement::create([
        'tenant_id' => $this->tenant->id,
        'slug' => '3_day_streak',
        'name' => ['da' => '3 dages streak'],
        'type' => 'streak',
        'points' => 500,
        'is_active' => true,
    ]);

    $achievement->rules()->create([
        'event' => 'user.checked_in',
        'operator' => '>=',
        'target' => 3,
    ]);

    // Day 1
    $checkIn1 = CheckIn::create(['user_id' => $this->user->id, 'tenant_id' => $this->tenant->id, 'checked_at' => now()->subDays(2)]);
    event(new UserCheckedIn($checkIn1));

    // Simulate time passing by updating progress updated_at
    $progress = $this->user->achievementProgress()->where('achievement_id', $achievement->id)->first();
    $progress->update(['updated_at' => now()->subDays(2)]);

    // Day 2
    $checkIn2 = CheckIn::create(['user_id' => $this->user->id, 'tenant_id' => $this->tenant->id, 'checked_at' => now()->subDay()]);
    event(new UserCheckedIn($checkIn2));
    $progress->refresh()->update(['updated_at' => now()->subDay()]);

    // Day 3
    $checkIn3 = CheckIn::create(['user_id' => $this->user->id, 'tenant_id' => $this->tenant->id, 'checked_at' => now()]);
    event(new UserCheckedIn($checkIn3));

    expect($progress->refresh()->progress)->toBe(3)
        ->and($this->user->refresh()->achievements)->toHaveCount(1);
});
