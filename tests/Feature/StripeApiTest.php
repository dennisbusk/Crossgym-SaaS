<?php

use App\Models\Tenant;
use App\Models\User;
use App\Services\Stripe\StripeService;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create([
        'stripe_secret_key' => 'sk_test_mock',
        'stripe_connect_account_id' => 'acct_mock',
    ]);

    $this->user = User::factory()->create([
        'tenant_id' => $this->tenant->id,
        'stripe_customer_id' => 'cus_mock',
    ]);

    $this->token = JWTAuth::fromUser($this->user);
});

it('returns a portal url', function () {
    $mockStripeService = Mockery::mock(StripeService::class);
    $mockStripeService->shouldReceive('createPortalSession')
        ->once()
        ->with('cus_mock', 'crossgym://home')
        ->andReturn(['url' => 'https://billing.stripe.com/p/session/mock']);

    $this->app->instance(StripeService::class, $mockStripeService);
    // Since StripeService::forTenant uses app(StripeService::class), we can mock it like this
    // but forTenant is static and returns a new instance via app().

    // Let's try to mock the app binding
    app()->bind(StripeService::class, function ($app, $parameters) use ($mockStripeService) {
        return $mockStripeService;
    });

    $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->postJson('/api/v1/stripe/portal', [
            'return_url' => 'crossgym://home',
        ]);

    $response->assertStatus(200)
        ->assertJson(['url' => 'https://billing.stripe.com/p/session/mock']);
});

it('returns a checkout url for subscription', function () {
    $mockStripeService = Mockery::mock(StripeService::class);
    $mockStripeService->shouldReceive('createSubscriptionCheckout')
        ->once()
        ->andReturn(['url' => 'https://checkout.stripe.com/c/session/mock_sub']);

    app()->bind(StripeService::class, function ($app, $parameters) use ($mockStripeService) {
        return $mockStripeService;
    });

    $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->postJson('/api/v1/stripe/checkout', [
            'price_id' => 'price_123',
            'mode' => 'subscription',
            'success_url' => 'crossgym://checkout-success',
            'cancel_url' => 'crossgym://checkout-cancel',
        ]);

    $response->assertStatus(200)
        ->assertJson(['url' => 'https://checkout.stripe.com/c/session/mock_sub']);
});

it('returns a checkout url for payment', function () {
    $mockStripeService = Mockery::mock(StripeService::class);
    $mockStripeService->shouldReceive('createPayment')
        ->once()
        ->andReturn(['url' => 'https://checkout.stripe.com/c/session/mock_pay']);

    app()->bind(StripeService::class, function ($app, $parameters) use ($mockStripeService) {
        return $mockStripeService;
    });

    $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->postJson('/api/v1/stripe/checkout', [
            'price_id' => 'price_123',
            'mode' => 'payment',
        ]);

    $response->assertStatus(200)
        ->assertJson(['url' => 'https://checkout.stripe.com/c/session/mock_pay']);
});
