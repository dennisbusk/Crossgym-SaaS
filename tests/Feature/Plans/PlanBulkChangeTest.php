<?php

declare(strict_types=1);

use App\Livewire\Admin\Plans\PlanShow;
use App\Models\Plan;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Livewire\Livewire;

it('bulk changes selected users to a new plan', function () {
    $tenant = Tenant::factory()->create();
    session(['tenant_id' => $tenant->id]);

    $role = Role::factory()->create(['slug' => 'superadmin', 'name' => 'SuperAdmin']);
    $admin = User::factory()->create(['tenant_id' => $tenant->id, 'role_id' => $role->id]);
    $this->actingAs($admin);

    $planA = Plan::factory()->create(['tenant_id' => $tenant->id, 'stripe_price_id' => 'price_A']);
    $planB = Plan::factory()->create(['tenant_id' => $tenant->id, 'stripe_price_id' => 'price_B']);

    $u1 = User::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Alpha']);
    $u2 = User::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Beta']);

    $s1 = Subscription::query()->create([
        'tenant_id' => $tenant->id,
        'user_id' => $u1->id,
        'plan_type' => 'subscription',
        'status' => 'active',
        'stripe_subscription_id' => 'sub_sel_1',
        'stripe_price_id' => $planA->stripe_price_id,
    ]);
    $s2 = Subscription::query()->create([
        'tenant_id' => $tenant->id,
        'user_id' => $u2->id,
        'plan_type' => 'subscription',
        'status' => 'trialing',
        'stripe_subscription_id' => 'sub_sel_2',
        'stripe_price_id' => $planA->stripe_price_id,
    ]);

    Livewire::test(PlanShow::class, ['plan' => $planA])
        ->set('targetPlanId', $planB->id)
        ->set("selected.{$u1->id}", true)
        ->call('bulkChangeConfirm')
        ->call('bulkChangeExecute')
        ->assertHasNoErrors();

    expect($s1->fresh()->stripe_price_id)->toBe($planB->stripe_price_id);
    expect($s2->fresh()->stripe_price_id)->toBe($planA->stripe_price_id);
});

it('bulk change can apply to all filtered users', function () {
    $tenant = Tenant::factory()->create();
    session(['tenant_id' => $tenant->id]);

    $role = Role::factory()->create(['slug' => 'superadmin', 'name' => 'SuperAdmin']);
    $admin = User::factory()->create(['tenant_id' => $tenant->id, 'role_id' => $role->id]);
    $this->actingAs($admin);

    $planA = Plan::factory()->create(['tenant_id' => $tenant->id, 'stripe_price_id' => 'price_A']);
    $planB = Plan::factory()->create(['tenant_id' => $tenant->id, 'stripe_price_id' => 'price_B']);

    $a1 = User::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Alice']);
    $a2 = User::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Alicia']);
    $b1 = User::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Bob']);

    $subs = collect([$a1, $a2, $b1]);
    foreach ($subs as $u) {
        Subscription::query()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $u->id,
            'plan_type' => 'subscription',
            'status' => 'active',
            'stripe_subscription_id' => 'sub_all_'.$u->id,
            'stripe_price_id' => $planA->stripe_price_id,
        ]);
    }

    Livewire::test(PlanShow::class, ['plan' => $planA])
        ->set('targetPlanId', $planB->id)
        ->set('search', 'Ali') // matches Alice + Alicia only
        ->set('includeAllFiltered', true)
        ->call('bulkChangeConfirm')
        ->call('bulkChangeExecute')
        ->assertHasNoErrors();

    expect($a1->subscription()->first()->stripe_price_id)->toBe($planB->stripe_price_id);
    expect($a2->subscription()->first()->stripe_price_id)->toBe($planB->stripe_price_id);
    expect($b1->subscription()->first()->stripe_price_id)->toBe($planA->stripe_price_id);
});
