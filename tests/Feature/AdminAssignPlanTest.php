<?php

declare(strict_types=1);

use App\Livewire\Admin\Users\UserForm;
use App\Models\Plan;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\User;
use App\Services\UserSubscriptionService;
use Illuminate\Support\Str;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

it('assigns subscription plan via service when saving user', function () {
    // Arrange: create a role and a subscription-type plan
    $adminRole = Role::query()->create(['name' => 'SuperAdmin', 'slug' => 'superadmin']);
    $admin = User::query()->create([
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'password' => 'secret-pass-123',
        'role_id' => $adminRole->id,
    ]);
    actingAs($admin);

    $role = Role::query()->create(['name' => 'Member', 'slug' => 'member']);
    $plan = Plan::query()->create([
        'tenant_id' => null,
        'stripe_price_id' => 'price_'.Str::random(8),
        'stripe_product_id' => 'prod_'.Str::random(8),
        'name' => 'Monthly',
        'amount' => 19900,
        'currency' => 'DKK',
        'interval' => 'month',
        'metadata' => ['plan_type' => 'subscription'],
    ]);

    // Mock service
    $mock = $this->mock(UserSubscriptionService::class);
    $mock->shouldReceive('assignSubscriptionPlan')
        ->once()
        ->andReturn(new Subscription);

    // Act
    Livewire::test(UserForm::class)
        ->set('name', 'Alice')
        ->set('email', 'alice@example.com')
        ->set('password', 'secret-pass-123')
        ->set('role_id', $role->id)
        ->set('plan_id', $plan->id)
        ->call('save')
        ->assertHasNoErrors();
});

it('initiates one-off checkout via service when saving user', function () {
    $adminRole = Role::query()->create(['name' => 'SuperAdmin', 'slug' => 'superadmin']);
    $admin = User::query()->create([
        'name' => 'Admin',
        'email' => 'admin2@example.com',
        'password' => 'secret-pass-123',
        'role_id' => $adminRole->id,
    ]);
    actingAs($admin);

    $role = Role::query()->create(['name' => 'Member', 'slug' => 'member']);
    $plan = Plan::query()->create([
        'tenant_id' => null,
        'stripe_price_id' => 'price_'.Str::random(8),
        'stripe_product_id' => 'prod_'.Str::random(8),
        'name' => '10 Credits',
        'amount' => 49900,
        'currency' => 'DKK',
        'interval' => 'one_time',
        'metadata' => ['plan_type' => 'one_off', 'total_booking_credits' => 10],
    ]);

    $mock = $this->mock(UserSubscriptionService::class);
    $mock->shouldReceive('assignOneOffPlan')
        ->once()
        ->andReturn('https://checkout.stripe.com/test');

    Livewire::test(UserForm::class)
        ->set('name', 'Bob')
        ->set('email', 'bob@example.com')
        ->set('password', 'secret-pass-123')
        ->set('role_id', $role->id)
        ->set('plan_id', $plan->id)
        ->call('save')
        ->assertHasNoErrors();
});
