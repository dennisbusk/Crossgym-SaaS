<?php

use App\Models\User;
use App\Models\Tenant;
use App\Models\Exercise;
use App\Models\WorkoutLog;
use Livewire\Livewire;
use App\Livewire\Components\ExerciseProgressWidget;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    session(['tenant_id' => $this->tenant->id]);
});

it('can see the exercise progress widget', function () {
    $this->actingAs($this->user);

    Livewire::test(ExerciseProgressWidget::class)
        ->assertStatus(200)
        ->assertSee(__('Exercise Progress'));
});

it('shows no data message when no logs exist', function () {
    $this->actingAs($this->user);

    Livewire::test(ExerciseProgressWidget::class)
        ->assertSee(__('No data available for the selected exercise.'));
});

it('loads chart data when logs exist', function () {
    $exercise = Exercise::create([
        'name' => ['da' => 'Squat'],
        'category' => 'strength',
        'tenant_id' => $this->tenant->id,
    ]);

    WorkoutLog::create([
        'user_id' => $this->user->id,
        'tenant_id' => $this->tenant->id,
        'exercise_id' => $exercise->id,
        'date' => now()->subDays(2),
        'weight' => 100,
    ]);

    WorkoutLog::create([
        'user_id' => $this->user->id,
        'tenant_id' => $this->tenant->id,
        'exercise_id' => $exercise->id,
        'date' => now()->subDay(),
        'weight' => 110,
    ]);

    $this->actingAs($this->user);

    Livewire::test(ExerciseProgressWidget::class)
        ->assertSet('exerciseId', $exercise->id)
        ->assertCount('chartData.data', 2)
        ->assertSee('Squat');
});

it('updates data when exercise is changed', function () {
     $exercise1 = Exercise::create([
        'name' => ['da' => 'Squat'],
        'category' => 'strength',
        'tenant_id' => $this->tenant->id,
    ]);

    $exercise2 = Exercise::create([
        'name' => ['da' => 'Bench Press'],
        'category' => 'strength',
        'tenant_id' => $this->tenant->id,
    ]);

    WorkoutLog::create([
        'user_id' => $this->user->id,
        'tenant_id' => $this->tenant->id,
        'exercise_id' => $exercise1->id,
        'date' => now()->subDay(),
        'weight' => 100,
    ]);

    WorkoutLog::create([
        'user_id' => $this->user->id,
        'tenant_id' => $this->tenant->id,
        'exercise_id' => $exercise2->id,
        'date' => now(),
        'weight' => 80,
    ]);

    $this->actingAs($this->user);

    Livewire::test(ExerciseProgressWidget::class)
        ->set('exerciseId', $exercise2->id)
        ->assertSet('chartData.data', [80.0]);
});
