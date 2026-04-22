<?php

declare(strict_types=1);

use App\Jobs\ReleaseUncheckedSeatsJob;
use App\Models\ClassType;
use App\Models\GymClass;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Carbon;

it('releases unchecked seats at class start while keeping checked-in participants', function () {
    // Freeze time to a deterministic point
    $now = Carbon::create(2025, 1, 1, 10, 0, 0);
    Carbon::setTestNow($now);

    // Arrange: tenant and class type
    $tenant = Tenant::factory()->create();
    $classType = ClassType::factory()->create();

    // Trainer and participants (same tenant)
    $trainer = User::factory()->withTenant($tenant->id)->create();
    $checkedInUser = User::factory()->withTenant($tenant->id)->create();
    $notCheckedInUser = User::factory()->withTenant($tenant->id)->create();

    // Class that starts now (inside lookback window)
    $startingClass = GymClass::create([
        'tenant_id' => $tenant->id,
        'name' => ['da' => 'Testhold', 'en' => 'Test Class'],
        'description' => ['da' => 'Beskrivelse', 'en' => 'Description'],
        'trainer_id' => $trainer->id,
        'class_type_id' => $classType->id,
        'max_participants' => 10,
        'class_start' => $now->copy(),
        'class_end' => $now->copy()->addHour(),
        'recurring_id' => null,
    ]);

    $checkedInRecord = \App\Models\CheckIn::create([
        'tenant_id' => $tenant->id,
        'user_id' => $checkedInUser->id,
        'gym_class_id' => $startingClass->id,
        'checked_at' => $now->copy()->subMinutes(5),
    ]);

    // Attach participants: one checked in, one not
    $startingClass->participants()->syncWithoutDetaching([
        $checkedInUser->id => ['check_in_id' => $checkedInRecord->id],
        $notCheckedInUser->id => ['check_in_id' => null],
    ]);

    // Control: class in the future should not be affected
    $futureClass = GymClass::create([
        'tenant_id' => $tenant->id,
        'name' => ['da' => 'Fremtid', 'en' => 'Future'],
        'description' => ['da' => 'Fremtid', 'en' => 'Future'],
        'trainer_id' => $trainer->id,
        'class_type_id' => $classType->id,
        'max_participants' => 10,
        'class_start' => $now->copy()->addHour(),
        'class_end' => $now->copy()->addHours(2),
        'recurring_id' => null,
    ]);
    $futureUser = User::factory()->withTenant($tenant->id)->create();
    $futureClass->participants()->syncWithoutDetaching([$futureUser->id => ['check_in_id' => null]]);

    // Control: class before lookback window should not be affected
    $pastOutsideLookback = GymClass::create([
        'tenant_id' => $tenant->id,
        'name' => ['da' => 'Fortid', 'en' => 'Past'],
        'description' => ['da' => 'Fortid', 'en' => 'Past'],
        'trainer_id' => $trainer->id,
        'class_type_id' => $classType->id,
        'max_participants' => 10,
        'class_start' => $now->copy()->subHours(1),
        'class_end' => $now->copy()->subMinutes(30),
        'recurring_id' => null,
    ]);
    $pastUser = User::factory()->withTenant($tenant->id)->create();
    $pastOutsideLookback->participants()->syncWithoutDetaching([$pastUser->id => ['check_in_id' => null]]);

    // Act: run the job with a 10-minute lookback for this tenant
    (new ReleaseUncheckedSeatsJob(lookbackMinutes: 10, tenantId: $tenant->id))->handle();

    // Refresh models/relations
    $startingClass->load('participants');
    $futureClass->load('participants');
    $pastOutsideLookback->load('participants');

    // Assert: unchecked participant for the started class was detached
    expect($startingClass->participants->pluck('id')->all())
        ->toContain($checkedInUser->id)
        ->not->toContain($notCheckedInUser->id);

    // Assert: future class unaffected
    expect($futureClass->participants->pluck('id')->all())
        ->toContain($futureUser->id);

    // Assert: past class outside lookback unaffected
    expect($pastOutsideLookback->participants->pluck('id')->all())
        ->toContain($pastUser->id);
});

it('only releases seats for the specified tenant when tenantId is passed', function () {
    $now = Carbon::create(2025, 1, 1, 10, 0, 0);
    Carbon::setTestNow($now);

    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();
    $classTypeA = ClassType::factory()->create(['tenant_id' => $tenantA->id]);
    $classTypeB = ClassType::factory()->create(['tenant_id' => $tenantB->id]);

    $trainerA = User::factory()->withTenant($tenantA->id)->create();
    $trainerB = User::factory()->withTenant($tenantB->id)->create();
    $userA = User::factory()->withTenant($tenantA->id)->create();
    $userB = User::factory()->withTenant($tenantB->id)->create();

    $classA = GymClass::create([
        'tenant_id' => $tenantA->id,
        'name' => ['da' => 'Class A'],
        'description' => ['da' => ''],
        'trainer_id' => $trainerA->id,
        'class_type_id' => $classTypeA->id,
        'max_participants' => 10,
        'class_start' => $now->copy(),
        'class_end' => $now->copy()->addHour(),
        'recurring_id' => null,
    ]);
    $classA->participants()->syncWithoutDetaching([$userA->id => ['check_in_id' => null]]);

    $classB = GymClass::create([
        'tenant_id' => $tenantB->id,
        'name' => ['da' => 'Class B'],
        'description' => ['da' => ''],
        'trainer_id' => $trainerB->id,
        'class_type_id' => $classTypeB->id,
        'max_participants' => 10,
        'class_start' => $now->copy(),
        'class_end' => $now->copy()->addHour(),
        'recurring_id' => null,
    ]);
    $classB->participants()->syncWithoutDetaching([$userB->id => ['check_in_id' => null]]);

    (new ReleaseUncheckedSeatsJob(lookbackMinutes: 10, tenantId: $tenantA->id))->handle();

    $classA->load('participants');
    $classB->load('participants');

    expect($classA->participants->pluck('id')->all())->not->toContain($userA->id);
    expect($classB->participants->pluck('id')->all())->toContain($userB->id);
});
