<?php

use App\Livewire\Admin\Dashboard as DashboardComponent;
use App\Livewire\Profile\Settings;
use App\Models\Dashboard;
use App\Models\Exercise;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserDashboardWidget;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;

beforeEach(function () {
    Artisan::call('permissions:sync');
    $this->tenant = Tenant::factory()->create();
    $this->role = Role::factory()->create(['slug' => 'test-role']);
    $this->user = User::factory()->create([
        'tenant_id' => $this->tenant->id,
        'role_id' => $this->role->id,
    ]);
});

it('shows only allowed widgets in profile settings', function () {
    // Give permission for revenue and bookings, but not subscribers
    $revenuePerm = Permission::where('model', 'Dashboard')->where('ability', 'view_revenue')->first();
    $bookingsPerm = Permission::where('model', 'Dashboard')->where('ability', 'view_bookings')->first();

    $this->user->permissions()->attach($revenuePerm, ['granted' => true]);
    $this->user->permissions()->attach($bookingsPerm, ['granted' => true]);

    $this->actingAs($this->user);

    Livewire::test(Settings::class)
        ->assertSee(__('Revenue (DKK)'))
        ->assertSee(__('Bookings'))
        ->assertDontSee(__('Total Subscribers (per Plan)'));
});

it('can toggle dashboard widgets in profile settings', function () {
    $revenuePerm = Permission::where('model', 'Dashboard')->where('ability', 'view_revenue')->first();
    $this->user->permissions()->attach($revenuePerm, ['granted' => true]);

    $this->actingAs($this->user);

    // Default should be true
    Livewire::test(Settings::class)
        ->assertSet('dashboardSettings.revenue', true)
        ->set('dashboardSettings.revenue', false)
        ->call('updateProfileInformation');

    $this->user->refresh();
    expect($this->user->dashboard_settings['revenue'])->toBeFalse();
});

it('respects toggled settings on the dashboard', function () {
    $revenuePerm = Permission::where('model', 'Dashboard')->where('ability', 'view_revenue')->first();
    $this->user->permissions()->attach($revenuePerm, ['granted' => true]);

    // Set setting to false
    $this->user->update(['dashboard_settings' => ['revenue' => false]]);

    $this->actingAs($this->user);

    Livewire::test(DashboardComponent::class)
        ->assertDontSee(__('Total Transactions'))
        ->assertDontSee(__('Total Revenue (DKK)'));

    // Set setting to true
    $this->user->update(['dashboard_settings' => ['revenue' => true]]);

    Livewire::test(DashboardComponent::class)
        ->assertSee(__('Total Transactions'))
        ->assertSee(__('Total Revenue (DKK)'));
});

it('can toggle dynamic widgets like exercise progress', function () {
    $exercise = Exercise::factory()->create(['tenant_id' => $this->tenant->id, 'name' => ['da' => 'Bænkpres']]);
    $widget = UserDashboardWidget::create([
        'user_id' => $this->user->id,
        'type' => 'exercise_progress',
        'settings' => ['exercise_id' => $exercise->id],
        'order' => 0,
    ]);

    $this->actingAs($this->user);

    // Should see in settings
    Livewire::test(Settings::class)
        ->assertSee(__('Exercise Progress').': Bænkpres')
        ->set('dashboardSettings.dw_'.$widget->id, false)
        ->call('updateProfileInformation');

    $this->user->refresh();
    expect($this->user->dashboard_settings['dw_'.$widget->id])->toBeFalse();

    // Should be hidden on dashboard
    Livewire::test(DashboardComponent::class)
        ->assertDontSee('components.exercise-progress-widget');

    // Toggle back on
    $this->user->update(['dashboard_settings' => ['dw_'.$widget->id => true]]);

    Livewire::test(DashboardComponent::class)
        ->assertSee('components.exercise-progress-widget');
});
