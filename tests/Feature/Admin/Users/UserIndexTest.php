<?php

use App\Livewire\Admin\Users\UserIndex;
use App\Models\Plan;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $role = Role::factory()->create(['name' => ['da' => 'superadmin'], 'slug' => 'superadmin']);
    $this->user = User::factory()->create(['role_id' => $role->id]);
    $this->actingAs($this->user);
    app()->instance('tenant', $this->user->tenant);
});

test('user index component can be rendered', function () {
    Livewire::test(UserIndex::class)
        ->assertStatus(200);
});

test('users can be filtered by role', function () {
    $role = Role::factory()->create(['name' => ['da' => 'Test Role'], 'slug' => 'test-role']);
    $userInRole = User::factory()->create(['role_id' => $role->id, 'name' => 'In Role User', 'tenant_id' => $this->user->tenant_id]);
    $userNotInRole = User::factory()->create(['name' => 'Not In Role User', 'tenant_id' => $this->user->tenant_id]);

    Livewire::test(UserIndex::class)
        ->set('roleFilter', $role->id)
        ->assertSee('In Role User')
        ->assertDontSee('Not In Role User');
});

test('users can be filtered by plan', function () {
    $plan = Plan::factory()->create(['name' => 'Test Plan', 'stripe_price_id' => 'price_123', 'tenant_id' => $this->user->tenant_id]);
    $userWithPlan = User::factory()->create(['name' => 'With Plan User', 'tenant_id' => $this->user->tenant_id]);
    Subscription::create([
        'user_id' => $userWithPlan->id,
        'tenant_id' => $userWithPlan->tenant_id,
        'stripe_subscription_id' => 'sub_123',
        'stripe_price_id' => $plan->stripe_price_id,
        'status' => 'active',
    ]);

    $userWithoutPlan = User::factory()->create(['name' => 'Without Plan User', 'tenant_id' => $this->user->tenant_id]);

    Livewire::test(UserIndex::class)
        ->set('planFilter', 'price_123')
        ->assertSee('With Plan User')
        ->assertDontSee('Without Plan User');
});

test('users can be filtered by subscription status', function () {
    $userActive = User::factory()->create(['name' => 'Active User', 'tenant_id' => $this->user->tenant_id]);
    Subscription::create([
        'user_id' => $userActive->id,
        'tenant_id' => $userActive->tenant_id,
        'stripe_subscription_id' => 'sub_active',
        'stripe_price_id' => 'price_active',
        'status' => 'active',
    ]);

    $userCanceled = User::factory()->create(['name' => 'Canceled User', 'tenant_id' => $this->user->tenant_id]);
    Subscription::create([
        'user_id' => $userCanceled->id,
        'tenant_id' => $userCanceled->tenant_id,
        'stripe_subscription_id' => 'sub_canceled',
        'stripe_price_id' => 'price_canceled',
        'status' => 'canceled',
    ]);

    Livewire::test(UserIndex::class)
        ->set('statusFilter', 'active')
        ->assertSee('Active User')
        ->assertDontSee('Canceled User');
});
