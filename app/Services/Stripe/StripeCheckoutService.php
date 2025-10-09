<?php

declare( strict_types=1 );

namespace App\Services\Stripe;

use App\Helpers\StripeHelper;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use RuntimeException;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\PaymentIntent;
use Stripe\Refund;

class StripeCheckoutService {

    public function createPayment( array $data ): CheckoutSession {
        $user     = $data['user'];
        $amount   = (int) $data['amount'];
        $currency = $data['currency'] ?? 'usd';
        $metadata = array_merge($data['metadata'] ?? [], [
            'tenant_id' => $user->tenant_id,
            'user_id'   => $user->id,
        ]);

        $client = StripeHelper::getStripeClient($user->tenant);

        $session = $client->checkout->sessions->create([
            'mode'                 => 'payment',
            'payment_method_types' => [ 'card' ],
            'success_url'          => $data['success_url'],
            'cancel_url'           => $data['cancel_url'],
            'currency'             => $currency,
            'metadata'             => $metadata,
            'line_items'           => [
                [
                    'price_data' => [
                        'currency'     => $currency,
                        'product_data' => [ 'name' => $data['name'] ?? 'Payment' ],
                        'unit_amount'  => $amount,
                    ],
                    'quantity'   => 1,
                ],
            ],
        ]);

        Payment::create([
            'tenant_id'                => $user->tenant_id,
            'user_id'                  => $user->id,
            'stripe_payment_intent_id' => $session->payment_intent ?? '',
            'stripe_session_id'        => $session->id,
            'amount'                   => $amount,
            'currency'                 => $currency,
            'status'                   => 'pending',
            'type'                     => 'payment',
            'metadata'                 => $metadata,
        ]);

        return $session;
    }

    public function capturePayment( string $paymentIntentId ): PaymentIntent {
        $client = StripeHelper::getStripeClient();

        return $client->paymentIntents->capture($paymentIntentId);
    }

    public function refundPayment( string $paymentIntentId, ?int $amount = null ): Refund {
        $client = StripeHelper::getStripeClient();
        $params = [ 'payment_intent' => $paymentIntentId ];
        if ( $amount ) {
            $params['amount'] = $amount;
        }

        return $client->refunds->create($params);
    }

    public function createSubscription( User $user, string $priceId ): CheckoutSession {
        $client = StripeHelper::getStripeClient($user->tenant);

        $session = $client->checkout->sessions->create([
            'mode'              => 'subscription',
            'success_url'       => url('/billing/success'),
            'cancel_url'        => url('/billing/cancel'),
            'customer_email'    => $user->email,
            'subscription_data' => [
                'metadata' => [
                    'tenant_id' => $user->tenant_id,
                    'user_id'   => $user->id,
                ],
            ],
            'line_items'        => [
                [
                    'price'    => $priceId,
                    'quantity' => 1,
                ],
            ],
        ]);

        return $session;
    }

    public function swapSubscription( User $user, string $newPriceId ): void {
        $client       = StripeHelper::getStripeClient($user->tenant);
        $subscription = Subscription::where('user_id', $user->id)
                                    ->where('tenant_id', $user->tenant_id)
                                    ->whereIn('status', [ 'active', 'trialing', 'past_due' ])
                                    ->first();
        if ( !$subscription ) {
            throw new RuntimeException(__('No active subscription to swap.'));
        }
        // Retrieve subscription from Stripe and update items
        $stripeSub = $client->subscriptions->retrieve($subscription->stripe_subscription_id);
        $itemId    = $stripeSub->items->data[0]->id ?? null;
        if ( !$itemId ) {
            throw new RuntimeException('Subscription item not found.');
        }
        $client->subscriptionItems->update($itemId, [
            'price'    => $newPriceId,
            'metadata' => [
                'tenant_id' => $user->tenant_id,
                'user_id'   => $user->id,
            ],
        ]);
        $subscription->update([ 'stripe_price_id' => $newPriceId ]);
    }

    public function cancelSubscription( User $user, bool $atPeriodEnd = true ): void {
        $client       = StripeHelper::getStripeClient($user->tenant);
        $subscription = Subscription::where('user_id', $user->id)
                                    ->where('tenant_id', $user->tenant_id)
                                    ->whereIn('status', [ 'active', 'trialing', 'past_due' ])
                                    ->first();
        if ( !$subscription ) {
            return; // nothing to cancel
        }
        $stripeSub = $client->subscriptions->update($subscription->stripe_subscription_id, [
            'cancel_at_period_end' => $atPeriodEnd,
        ]);
        $subscription->update([
            'cancel_at_period_end' => (bool) $stripeSub->cancel_at_period_end,
            'status'               => $stripeSub->status,
            'current_period_end'   => isset($stripeSub->current_period_end) ? Carbon::createFromTimestamp($stripeSub->current_period_end) : null,
        ]);
    }

    public function resumeSubscription( User $user ): void {
        $client       = StripeHelper::getStripeClient($user->tenant);
        $subscription = Subscription::where('user_id', $user->id)
                                    ->where('tenant_id', $user->tenant_id)
                                    ->where('status', 'active')
                                    ->first();
        if ( !$subscription ) {
            return;
        }
        $stripeSub = $client->subscriptions->update($subscription->stripe_subscription_id, [
            'cancel_at_period_end' => false,
        ]);
        $subscription->update([
            'cancel_at_period_end' => false,
            'status'               => $stripeSub->status,
            'current_period_end'   => isset($stripeSub->current_period_end) ? Carbon::createFromTimestamp($stripeSub->current_period_end) : null,
        ]);
    }

    public function handleWebhook( Request $request ): void {
        // Controller will perform signature verification and pass the event to domain logic if needed.
        // This method kept for potential future use.
    }
}
