<?php

declare(strict_types=1);

use App\Livewire\Admin\Plans\PlanShow;
use App\Models\Plan;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Livewire\Livewire;

it('lists users on the plan and supports search filtering', function () {
    // Arrange: tenant and auth
    $tenant = Tenant::factory()->create();
    session(['tenant_id' => $tenant->id]);

    $role = Role::factory()->create(['slug' => 'superadmin', 'name' => 'SuperAdmin']);
    $admin = User::factory()->create(['tenant_id' => $tenant->id, 'role_id' => $role->id]);
    $this->actingAs($admin);

    $plan = Plan::factory()->create(['tenant_id' => $tenant->id, 'stripe_price_id' => 'price_A']);

    // Users on plan
    $u1 = User::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Alice Alpha', 'email' => 'alice@example.com']);
    $u2 = User::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Bob Beta', 'email' => 'bob@example.com']);
    Subscription::query()->create([
        'tenant_id' => $tenant->id,
        'user_id' => $u1->id,
        'plan_type' => 'subscription',
        'status' => 'active',
        'stripe_subscription_id' => 'sub_1',
        'stripe_price_id' => $plan->stripe_price_id,
    ]);
    Subscription::query()->create([
        'tenant_id' => $tenant->id,
        'user_id' => $u2->id,
        'plan_type' => 'subscription',
        'status' => 'trialing',
        'stripe_subscription_id' => 'sub_2',
        'stripe_price_id' => $plan->stripe_price_id,
    ]);

    // Another user not on plan
    $u3 = User::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Charlie']);

    // Act + Assert: initial list has Alice and Bob
    Livewire::test(PlanShow::class, ['plan' => $plan])
        ->assertOk()
        ->assertSee('Alice Alpha')
        ->assertSee('Bob Beta')
        ->assertDontSee('Charlie')
        // Filter by search
        ->set('search', 'Alice')
        ->assertSee('Alice Alpha')
        ->assertDontSee('Bob Beta');
});
