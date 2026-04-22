<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Jobs\ImportStripeProductsJob;
use App\Models\Plan;
use App\Models\Tenant;
use App\Services\Stripe\StripeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ImportStripeProductsJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_products_and_prices_from_stripe_for_a_tenant()
    {
        // Arrange
        $tenant = Tenant::factory()->create([
            'stripe_secret_key' => 'sk_test_123',
            'stripe_connect_account_id' => 'acct_123',
        ]);

        $mockStripeService = Mockery::mock(StripeService::class);

        $stripeData = [
            'products' => [
                [
                    'id' => 'prod_1',
                    'name' => 'Gold Plan',
                    'metadata' => ['description' => 'Gold plan description'],
                ],
                [
                    'id' => 'prod_2',
                    'name' => 'Silver Plan',
                    'metadata' => ['description' => 'Silver plan description'],
                ],
            ],
            'prices' => [
                [
                    'id' => 'price_1',
                    'product' => 'prod_1',
                    'unit_amount' => 10000,
                    'currency' => 'dkk',
                    'recurring' => ['interval' => 'month'],
                    'metadata' => ['price_meta' => 'value1'],
                ],
                [
                    'id' => 'price_2',
                    'product' => 'prod_2',
                    'unit_amount' => 5000,
                    'currency' => 'dkk',
                    'recurring' => ['interval' => 'month'],
                    'metadata' => [],
                ],
                [
                    'id' => 'price_3',
                    'product' => 'prod_2', // Second price for same product
                    'unit_amount' => 50000,
                    'currency' => 'dkk',
                    'recurring' => ['interval' => 'year'],
                    'metadata' => [],
                ],
            ],
        ];

        $mockStripeService->shouldReceive('getPlans')
            ->once()
            ->andReturn($stripeData);

        // Bind the mock to the container
        $this->app->bind(StripeService::class, function () use ($mockStripeService) {
            return $mockStripeService;
        });

        // Act
        (new ImportStripeProductsJob($tenant))->handle();

        // Assert
        $this->assertDatabaseCount('plans', 3);

        $this->assertDatabaseHas('plans', [
            'tenant_id' => $tenant->id,
            'stripe_price_id' => 'price_1',
            'stripe_product_id' => 'prod_1',
            'name' => 'Gold Plan',
            'amount' => 10000,
            'currency' => 'DKK',
            'interval' => 'month',
        ]);

        $plan1 = Plan::where('stripe_price_id', 'price_1')->first();
        $this->assertEquals('Gold plan description', $plan1->metadata['description']);
        $this->assertEquals('value1', $plan1->metadata['price_meta']);

        $this->assertDatabaseHas('plans', [
            'tenant_id' => $tenant->id,
            'stripe_price_id' => 'price_2',
            'stripe_product_id' => 'prod_2',
            'name' => 'Silver Plan',
            'amount' => 5000,
            'currency' => 'DKK',
            'interval' => 'month',
        ]);

        $this->assertDatabaseHas('plans', [
            'tenant_id' => $tenant->id,
            'stripe_price_id' => 'price_3',
            'stripe_product_id' => 'prod_2',
            'name' => 'Silver Plan',
            'amount' => 50000,
            'currency' => 'DKK',
            'interval' => 'year',
        ]);
    }

    public function test_it_only_imports_for_the_specified_tenant()
    {
        // Arrange
        $tenant1 = Tenant::factory()->create(['stripe_connect_account_id' => 'acct_1']);
        $tenant2 = Tenant::factory()->create(['stripe_connect_account_id' => 'acct_2']);

        $mockStripeService = Mockery::mock(StripeService::class);

        // Expect getPlans to be called ONLY for tenant1
        $mockStripeService->shouldReceive('getPlans')
            ->once()
            ->andReturn(['products' => [], 'prices' => []]);

        $this->app->bind(StripeService::class, function () use ($mockStripeService) {
            return $mockStripeService;
        });

        // Act
        (new ImportStripeProductsJob($tenant1))->handle();

        // Assert
        // Verified by expectation on mock
        $this->assertTrue(true);
    }
}
