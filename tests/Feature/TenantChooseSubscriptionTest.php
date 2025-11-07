<?php

declare(strict_types=1);

use App\Livewire\Admin\TenantChooseSubscription;
use App\Models\SubscriptionOption;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Stripe\StripeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function actingAsWithTenant(?User $user = null, ?Tenant $tenant = null): User {
    $tenant = $tenant ?? Tenant::factory()->create();
    $user = $user ?? User::factory()->create(['tenant_id' => $tenant->id]);
    test()->actingAs($user);
    test()->withSession(['tenant_id' => $tenant->id]);
    return $user;
}

it('tenant_can_view_subscription_options', function () {
    // options come from migration seed
    $user = actingAsWithTenant();

    Livewire::test(TenantChooseSubscription::class)
        ->assertStatus(200)
        ->assertSee('.5% of each transaction')
        ->assertSee('2kr per active member');
});

it('tenant_can_select_subscription_option', function () {
    $user = actingAsWithTenant();

    $option = SubscriptionOption::query()->where('active', true)->first();
    expect($option)->not()->toBeNull();

    Livewire::test(TenantChooseSubscription::class)
        ->call('select', $option->id)
        ->call('confirm')
        ->assertSessionHas('banner');

    $tenant = Tenant::find(session('tenant_id'));
    expect($tenant->subscription_option_id)->toBe($option->id);
});

it('stripe_metadata_is_updated_on_selection', function () {
    $tenant = Tenant::factory()->create([
        'stripe_connect_account_id' => 'acct_123',
    ]);
    $user = actingAsWithTenant(tenant: $tenant);

    $mock = Mockery::mock(StripeService::class);
    $mock->shouldReceive('updateConnectedAccountMetadata')
        ->once()
        ->withArgs(function ($t, $metadata) use ($tenant) {
            return $t->id === $tenant->id && isset($metadata['subscription_option_id']);
        })
        ->andReturn(['id' => 'acct_123']);
    $this->app->instance(StripeService::class, $mock);

    $option = SubscriptionOption::query()->first();

    Livewire::test(TenantChooseSubscription::class)
        ->call('select', $option->id)
        ->call('confirm')
        ->assertSessionHas('banner');
});

it('only_authenticated_users_can_access_route', function () {
    $this->get(route('tenant.subscription'))
        ->assertRedirect('/login');
});
