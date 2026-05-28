<?php

use App\Livewire\Profile\WorkoutLogForm;
use App\Livewire\Profile\WorkoutLogIndex;
use App\Models\Exercise;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WorkoutLog;
use Livewire\Livewire;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    session(['tenant_id' => $this->tenant->id]);
});

it('can view workout logs index', function () {
    $this->actingAs($this->user);

    Livewire::test(WorkoutLogIndex::class)
        ->assertStatus(200)
        ->assertSee(__('Workout Log'));
});

it('can create a workout log entry with a new exercise', function () {
    $this->actingAs($this->user);

    Livewire::test(WorkoutLogForm::class)
        ->set('date', '2026-04-22')
        ->set('new_exercise_name', 'Bench Press')
        ->set('category', 'strength')
        ->set('weight', 80)
        ->set('reps', 10)
        ->set('sets', 3)
        ->call('save')
        ->assertRedirect(route('workout-logs.index'));

    $this->assertDatabaseHas('exercises', [
        'category' => 'strength',
        'tenant_id' => $this->tenant->id,
    ]);

    $this->assertDatabaseHas('workout_logs', [
        'user_id' => $this->user->id,
        'weight' => 80,
        'reps' => 10,
        'sets' => 3,
    ]);
});

it('can edit a workout log entry', function () {
    $exercise = Exercise::create([
        'name' => ['da' => 'Run'],
        'category' => 'cardio',
        'tenant_id' => $this->tenant->id,
    ]);

    $log = WorkoutLog::create([
        'user_id' => $this->user->id,
        'tenant_id' => $this->tenant->id,
        'exercise_id' => $exercise->id,
        'date' => '2026-04-22',
        'distance' => 5,
        'duration' => 1800,
    ]);

    $this->actingAs($this->user);

    Livewire::test(WorkoutLogForm::class, ['workoutLog' => $log])
        ->set('distance', 6)
        ->call('save')
        ->assertRedirect(route('workout-logs.index'));

    $this->assertEquals(6, $log->fresh()->distance);
});

it('cannot see other users logs', function () {
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
        'date' => '2026-04-22',
    ]);

    $this->actingAs($this->user);

    Livewire::test(WorkoutLogIndex::class)
        ->assertDontSee('Deadlift');
});
