<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Plan;
use App\Models\Tenant;
use App\Services\Stripe\StripePlanService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class ImportStripeProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300];

    public int $timeout = 300;

    /**
     * @param  Tenant  $tenant  The tenant to import products for.
     */
    public function __construct(
        public Tenant $tenant
    ) {}

    public function handle(): void
    {
        try {
            $this->importForTenant($this->tenant);
        } catch (\Throwable $e) {
            Log::error('ImportStripeProductsJob failed for tenant '.$this->tenant->id, [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    protected function importForTenant(Tenant $tenant): void
    {
        $stripePlanService = StripePlanService::make($tenant);

        // We use getPlans which returns both active products and prices
        $data = $stripePlanService->getPlans();

        $prices = $data['prices'] ?? [];
        $products = collect($data['products'] ?? [])->keyBy('id');

        $count = 0;
        foreach ($prices as $priceData) {
            $productId = is_string($priceData['product']) ? $priceData['product'] : ($priceData['product']['id'] ?? null);
            $product = $products->get($productId) ?? (is_array($priceData['product']) ? $priceData['product'] : null);

            if (! $product) {
                continue;
            }

            Plan::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'stripe_price_id' => $priceData['id'],
                ],
                [
                    'stripe_product_id' => $productId,
                    'name' => $product['name'] ?? 'Stripe Plan',
                    'amount' => (int) ($priceData['unit_amount'] ?? 0),
                    'currency' => strtoupper($priceData['currency'] ?? 'DKK'),
                    'interval' => Arr::get($priceData, 'recurring.interval', 'one_time') ?? 'one_time',
                    'metadata' => array_merge($product['metadata'] ?? [], $priceData['metadata'] ?? []),
                ]
            );
            $count++;
        }

        Log::info("ImportStripeProductsJob: Imported {$count} plans for tenant {$tenant->id}");
    }

    public function failed(\Throwable $e): void
    {
        Log::error('ImportStripeProductsJob failed', [
            'tenant_id' => $this->tenant->id,
            'error' => $e->getMessage(),
        ]);
    }
}
