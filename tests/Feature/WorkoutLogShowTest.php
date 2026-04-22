<?php

use App\Models\User;
use App\Models\Tenant;
use App\Models\Exercise;
use App\Models\WorkoutLog;
use Livewire\Livewire;
use App\Livewire\Profile\WorkoutLogShow;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    session(['tenant_id' => $this->tenant->id]);
});

it('can view a workout log', function () {
    $exercise = Exercise::create([
        'name' => ['da' => 'Deadlift'],
        'category' => 'strength',
        'tenant_id' => $this->tenant->id,
    ]);

    $log = WorkoutLog::create([
        'user_id' => $this->user->id,
        'tenant_id' => $this->tenant->id,
        'exercise_id' => $exercise->id,
        'date' => now(),
        'weight' => 150,
        'reps' => 5,
        'sets' => 3,
        'notes' => 'Feeling strong today',
    ]);

    $this->actingAs($this->user);

    Livewire::test(WorkoutLogShow::class, ['workoutLog' => $log])
        ->assertSee('Deadlift')
        ->assertSee('150 kg')
        ->assertSee('5')
        ->assertSee('3')
        ->assertSee('Feeling strong today')
        ->assertSee('Rediger') // Edit in Danish
        ->assertSee('Tilbage'); // Back in Danish
});

it('cannot view someone else workout log', function () {
    $otherUser = User::factory()->create(['tenant_id' => $this->tenant->id]);

    $exercise = Exercise::create([
        'name' => ['da' => 'Deadlift'],
        'category' => 'strength',
        'tenant_id' => $this->tenant->id,
    ]);

    $log = WorkoutLog::create([
        'user_id' => $otherUser->id,
        'tenant_id' => $this->tenant->id,
        'exercise_id' => $exercise->id,
        'date' => now(),
        'weight' => 150,
    ]);

    $this->actingAs($this->user);

    Livewire::test(WorkoutLogShow::class, ['workoutLog' => $log])
        ->assertStatus(403);
});
