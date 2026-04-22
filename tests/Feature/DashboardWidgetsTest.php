<?php

use App\Models\User;
use App\Models\Tenant;
use App\Models\Exercise;
use App\Models\WorkoutLog;
use App\Models\UserDashboardWidget;
use Livewire\Livewire;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Components\ExerciseProgressWidget;
use App\Livewire\Components\PersonalRecordWidget;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    session(['tenant_id' => $this->tenant->id]);
});

it('can add an exercise progress widget to dashboard', function () {
    $exercise = Exercise::create([
        'name' => ['da' => 'Squat'],
        'category' => 'strength',
        'tenant_id' => $this->tenant->id,
    ]);

    $this->actingAs($this->user);

    Livewire::test(Dashboard::class)
        ->call('addExerciseWidget', $exercise->id)
        ->assertDispatched('widget-added');

    $this->assertDatabaseHas('user_dashboard_widgets', [
        'user_id' => $this->user->id,
        'type' => 'exercise_progress',
        'settings->exercise_id' => $exercise->id,
    ]);
});

it('can add a personal record widget to dashboard', function () {
    $this->actingAs($this->user);

    Livewire::test(Dashboard::class)
        ->call('addPrWidget')
        ->assertDispatched('widget-added');

    $this->assertDatabaseHas('user_dashboard_widgets', [
        'user_id' => $this->user->id,
        'type' => 'personal_record',
    ]);
});

it('can remove a widget from dashboard', function () {
    $widget = UserDashboardWidget::create([
        'user_id' => $this->user->id,
        'type' => 'personal_record',
        'settings' => [],
    ]);

    $this->actingAs($this->user);

    Livewire::test(PersonalRecordWidget::class, ['widgetId' => $widget->id])
        ->call('remove')
        ->assertDispatched('widget-removed');

    $this->assertDatabaseMissing('user_dashboard_widgets', ['id' => $widget->id]);
});

it('shows personal records in the PR widget', function () {
    $exercise = Exercise::create([
        'name' => ['da' => 'Bench Press'],
        'category' => 'strength',
        'tenant_id' => $this->tenant->id,
    ]);

    WorkoutLog::create([
        'user_id' => $this->user->id,
        'tenant_id' => $this->tenant->id,
        'exercise_id' => $exercise->id,
        'date' => now(),
        'weight' => 100,
    ]);

    $this->actingAs($this->user);

    Livewire::test(PersonalRecordWidget::class)
        ->assertSee('Bench Press')
        ->assertSee('100 kg')
        ->assertSee(now()->format('d/m/Y'))
        ->assertDontSee('workout-logs/1/edit')
        ->assertSee('workout-logs/1');
});

it('shows cardio personal records in the PR widget', function () {
    $exercise = Exercise::create([
        'name' => ['da' => '5km Run'],
        'category' => 'cardio',
        'tenant_id' => $this->tenant->id,
    ]);

    WorkoutLog::create([
        'user_id' => $this->user->id,
        'tenant_id' => $this->tenant->id,
        'exercise_id' => $exercise->id,
        'date' => now(),
        'distance' => 5,
        'duration' => 1200, // 20 mins
    ]);

    $this->actingAs($this->user);

    Livewire::test(PersonalRecordWidget::class)
        ->assertSee('5km Run')
        ->assertSee('5 km')
        ->assertSee('20:00')
        ->assertSee(now()->format('d/m/Y'));
});
