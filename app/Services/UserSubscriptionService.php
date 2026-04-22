<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Stripe\StripeService;
use App\Services\Stripe\StripeTenantClient;

class UserSubscriptionService
{
    public function __construct(
        protected StripeService $stripe
    ) {}

    public static function make(): self
    {
        return new self(StripeService::forTenant());
    }

    public function ensureStripeCustomer(User $user): string
    {
        if ($user->stripe_customer_id) {
            return $user->stripe_customer_id;
        }
        $client = (new StripeTenantClient)->client();
        $opts = (new StripeTenantClient)->options();
        $customer = $client->customers->create([
            'email' => $user->email,
            'name' => $user->name,
            'metadata' => [
                'user_id' => $user->id,
                'tenant_id' => tenant()?->id,
            ],
        ], $opts);
        $user->forceFill(['stripe_customer_id' => $customer->id])->save();

        return $customer->id;
    }

    /**
     * Assign or swap a recurring subscription plan for the user.
     * Returns the updated Subscription model.
     */
    public function assignSubscriptionPlan(User $user, Plan $plan): Subscription
    {
        $customerId = $this->ensureStripeCustomer($user);
        $client = (new StripeTenantClient)->client();
        $opts = (new StripeTenantClient)->options();

        // Always align recurring billing to the 1st of next month
        $anchor = now()->startOfMonth()->addMonth()->startOfDay();
        $anchorTs = $anchor->getTimestamp();

        // Find existing Stripe subscription if any
        $sub = Subscription::query()->where('tenant_id', tenant()?->id)
            ->where('user_id', $user->id)
            ->first();

        if ($sub && $sub->stripe_subscription_id) {
            // Swap price on existing subscription
            $stripeSub = $client->subscriptions->retrieve($sub->stripe_subscription_id, [], $opts);
            $itemId = $stripeSub->items->data[0]->id ?? null;
            if ($itemId) {
                // Determine if this is an upgrade (more expensive) to apply immediately with proration
                $currentPlan = Plan::query()->where('stripe_price_id', (string) $sub->stripe_price_id)->first();
                $isUpgrade = false;
                if ($currentPlan && $plan) {
                    // Amounts are expected to be stored in the smallest currency unit (e.g., cents)
                    $currentAmount = (int) ($currentPlan->amount ?? 0);
                    $newAmount = (int) ($plan->amount ?? 0);
                    $isUpgrade = $newAmount > $currentAmount;
                }
                // Determine if current anchor is already the 1st; if not, re-anchor to next 1st
                $currentPeriodEnd = $stripeSub->current_period_end ?? null;
                $currentAnchorDay = null;
                if ($currentPeriodEnd) {
                    // For monthly plans, the billing anchor day is typically the day-of-month of the cycle start
                    // We can infer the anchor day from the period start
                    $currentPeriodStart = $stripeSub->current_period_start ?? null;
                    if ($currentPeriodStart) {
                        $currentAnchorDay = (int) \Carbon\Carbon::createFromTimestamp($currentPeriodStart)->day;
                    }
                }

                $updatePayload = [
                    'items' => [[
                        'id' => $itemId,
                        'price' => $plan->stripe_price_id,
                    ]],
                    'metadata' => [
                        'user_id' => $user->id,
                        'plan_type' => 'subscription',
                    ],
                    // Ensure prorations are created on swap
                    'proration_behavior' => 'create_prorations',
                ];

                if ($isUpgrade) {
                    // Upgrades take effect immediately, charge prorated difference now
                    $updatePayload['billing_cycle_anchor'] = 'unchanged';
                    // Attempt payment immediately if an invoice is generated
                    $updatePayload['payment_behavior'] = 'pending_if_incomplete';
                } else {
                    // Downgrades or same-price: move the anchor to the 1st of next month if not already
                    if ($currentAnchorDay !== 1) {
                        $updatePayload['billing_cycle_anchor'] = $anchorTs;
                    } else {
                        // Keep existing anchor
                        $updatePayload['billing_cycle_anchor'] = 'unchanged';
                    }
                }

                $updated = $client->subscriptions->update($stripeSub->id, $updatePayload, $opts);
            }

            $sub->fill([
                'stripe_price_id' => $plan->stripe_price_id,
                'plan_type' => 'subscription',
                // Try to persist the latest known period end if available
                'current_period_end' => isset($updated->current_period_end) ? \Carbon\Carbon::createFromTimestamp($updated->current_period_end) : $sub->current_period_end,
                'status' => (string) ($updated->status ?? $sub->status),
            ])->save();

            return $sub;
        }

        // Create new subscription
        $created = $client->subscriptions->create([
            'customer' => $customerId,
            'items' => [['price' => $plan->stripe_price_id]],
            'metadata' => [
                'user_id' => $user->id,
                'plan_type' => 'subscription',
            ],
            'payment_behavior' => 'default_incomplete',
            // Align billing to the 1st of next month and charge a prorated amount now
            'billing_cycle_anchor' => $anchorTs,
            'proration_behavior' => 'create_prorations',
            'expand' => ['latest_invoice.payment_intent'],
        ], $opts);

        return Subscription::query()->updateOrCreate(
            ['tenant_id' => tenant()?->id, 'user_id' => $user->id],
            [
                'stripe_subscription_id' => $created->id,
                'stripe_price_id' => $plan->stripe_price_id,
                'status' => (string) ($created->status ?? 'incomplete'),
                'plan_type' => 'subscription',
            ]
        );
    }

    /**
     * Initiate a one-off plan purchase via Checkout. Returns checkout URL.
     */
    public function assignOneOffPlan(User $user, Plan $plan): string
    {
        $this->ensureStripeCustomer($user);

        $meta = (array) ($plan->metadata ?? []);
        $lineItems = [[
            'price' => $plan->stripe_price_id,
            'quantity' => 1,
        ]];

        $created = $this->stripe->createPayment([
            'mode' => 'payment',
            'line_items' => $lineItems,
            'success_url' => url('/admin/users'),
            'cancel_url' => url('/admin/users'),
            // Show a Continue button on the hosted success page that sends the admin back
            'continue_url' => url('/admin/users'),
            'customer_email' => $user->email,
            'metadata' => [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'plan_type' => 'one_off',
                'credits' => (int) ($meta['total_booking_credits'] ?? 0),
            ],
        ]);

        // Ensure a local subscription row exists (plan_type one_off)
        Subscription::query()->updateOrCreate(
            ['tenant_id' => tenant()?->id, 'user_id' => $user->id],
            [
                'stripe_price_id' => $plan->stripe_price_id,
                'plan_type' => 'one_off',
                'status' => 'one_off',
            ]
        );

        return (string) ($created['url'] ?? '');
    }

    public function cancelSubscription(User $user): void
    {
        $sub = Subscription::query()->where('tenant_id', tenant()?->id)
            ->where('user_id', $user->id)
            ->first();
        if (! $sub || ! $sub->stripe_subscription_id) {
            return;
        }

        $client = (new StripeTenantClient)->client();
        $opts = (new StripeTenantClient)->options();
        $client->subscriptions->update($sub->stripe_subscription_id, [
            'cancel_at_period_end' => true,
        ], $opts);

        $sub->update(['cancel_at_period_end' => true]);
    }
}
