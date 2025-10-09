<?php

declare( strict_types=1 );

namespace App\Services\Stripe;

use App\Helpers\StripeHelper;
use App\Models\Plan;
use App\Models\Tenant;

class StripePlanService {

    public function getPlans( ?Tenant $tenant = null ): array {
        $tenant = $tenant ?? ( app()->has('tenant') ? app('tenant') : null );
        $client = StripeHelper::getStripeClient($tenant);
        $prices = $client->prices->all([ 'active' => true, 'expand' => [ 'data.product' ] ]);

        $result = [];
        foreach ( $prices->data as $price ) {
            if ( $price->type !== 'recurring' ) {
                continue;
            }
            $result[] = [
                'stripe_price_id' => $price->id,
                'name'            => $price->product->name ?? 'Plan',
                'amount'          => $price->unit_amount,
                'currency'        => $price->currency,
                'interval'        => $price->recurring->interval,
                'metadata'        => (array) ( $price->metadata ?? [] ),
            ];
        }

        // Cache locally in plans table (upsert)
        foreach ( $result as $planData ) {
            Plan::updateOrCreate(
                [ 'tenant_id' => $tenant?->id, 'stripe_price_id' => $planData['stripe_price_id'] ],
                [
                    'name'     => $planData['name'],
                    'amount'   => $planData['amount'],
                    'currency' => $planData['currency'],
                    'interval' => $planData['interval'],
                    'metadata' => $planData['metadata'],
                ]
            );
        }

        return $result;
    }

    public function createPlan( string $name, int $amount, string $interval, ?Tenant $tenant = null ): array {
        $tenant = $tenant ?? ( app()->has('tenant') ? app('tenant') : null );
        $client = StripeHelper::getStripeClient($tenant);

        // Create product
        $product = $client->products->create([
            'name'     => $name,
            'metadata' => [
                'tenant_id' => $tenant?->id,
            ],
        ]);

        // Create price
        $price = $client->prices->create([
            'unit_amount' => $amount,
            'currency'    => 'usd',
            'recurring'   => [ 'interval' => $interval ],
            'product'     => $product->id,
            'metadata'    => [
                'tenant_id' => $tenant?->id,
            ],
        ]);

        Plan::updateOrCreate(
            [ 'tenant_id' => $tenant?->id, 'stripe_price_id' => $price->id ],
            [
                'name'     => $name,
                'amount'   => $amount,
                'currency' => $price->currency,
                'interval' => $interval,
                'metadata' => [],
            ]
        );

        return [
            'stripe_price_id' => $price->id,
            'name'            => $name,
            'amount'          => $amount,
            'currency'        => $price->currency,
            'interval'        => $interval,
            'metadata'        => [],
        ];
    }
}
