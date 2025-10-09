<?php

declare( strict_types=1 );

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Tenant;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Stripe\Webhook;
use Throwable;

class StripeWebhookController extends Controller {

    public function handle( Request $request ) {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        $tenant = $this->detectTenantFromPayload($payload) ?? $this->detectTenantFromQuery($request);

        $event         = null;
        $webhookSecret = $tenant?->stripe_webhook_secret;

        // If tenant not known yet, try all webhook secrets (fallback minimal implementation)
        if ( !$tenant || !$webhookSecret ) {
            $event = $this->tryVerifyAgainstAllTenants($payload, $sigHeader);
            // If verified, backfill tenant by metadata
            if ( $event ) {
                $tenant = $this->detectTenantFromEvent($event);
            }
        }
        else {
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        }

        // Log raw webhook payload
        $logId = DB::table('stripe_webhook_logs')->insertGetId([
            'tenant_id'  => $tenant?->id,
            'event_id'   => $event?->id ?? null,
            'type'       => $event?->type ?? null,
            'payload'    => $payload,
            'status'     => 'received',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            if ( !$event ) {
                throw new RuntimeException('Unable to verify Stripe event.');
            }

            switch ( $event->type ) {
                case 'checkout.session.completed':
                    $this->onCheckoutSessionCompleted($event->data->object);
                    break;
                case 'payment_intent.succeeded':
                    $this->onPaymentSucceeded($event->data->object);
                    break;
                case 'payment_intent.payment_failed':
                    $this->onPaymentFailed($event->data->object);
                    break;
                case 'charge.refunded':
                    $this->onChargeRefunded($event->data->object);
                    break;
                case 'invoice.payment_succeeded':
                    $this->onInvoicePaymentSucceeded($event->data->object);
                    break;
                case 'invoice.payment_failed':
                    $this->onInvoicePaymentFailed($event->data->object);
                    break;
                case 'customer.subscription.created':
                case 'customer.subscription.updated':
                case 'customer.subscription.deleted':
                    $this->onSubscriptionEvent($event->data->object);
                    break;
            }

            DB::table('stripe_webhook_logs')->where('id', $logId)->update([
                'status'     => 'processed',
                'updated_at' => now(),
            ]);
        } catch ( Throwable $e ) {
            DB::table('stripe_webhook_logs')->where('id', $logId)->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
                'updated_at'    => now(),
            ]);
            Log::error('Stripe webhook failed: ' . $e->getMessage());

            return response()->json([ 'error' => $e->getMessage() ], 400);
        }

        return response()->json([ 'status' => 'ok' ]);
    }

    protected function detectTenantFromPayload( string $payload ): ?Tenant {
        $data     = json_decode($payload, true);
        $tenantId = $data['data']['object']['metadata']['tenant_id'] ?? null;
        if ( $tenantId ) {
            return Tenant::find($tenantId);
        }

        return null;
    }

    protected function detectTenantFromQuery( Request $request ): ?Tenant {
        $tenantId = $request->query('tenant_id');
        if ( $tenantId ) {
            return Tenant::find($tenantId);
        }

        return null;
    }

    protected function tryVerifyAgainstAllTenants( string $payload, ?string $sigHeader ): ?object {
        if ( !$sigHeader ) {
            return null;
        }
        /** @var Collection $tenants */
        $tenants = Tenant::whereNotNull('stripe_webhook_secret')->get([ 'id', 'stripe_webhook_secret' ]);
        foreach ( $tenants as $tenant ) {
            try {
                return Webhook::constructEvent($payload, $sigHeader, $tenant->stripe_webhook_secret);
            } catch ( Throwable $e ) {
                // try next
            }
        }

        return null;
    }

    protected function detectTenantFromEvent( object $event ): ?Tenant {
        $obj      = $event->data->object ?? null;
        $tenantId = $obj->metadata->tenant_id ?? null;
        if ( $tenantId ) {
            return Tenant::find($tenantId);
        }

        return null;
    }

    protected function onCheckoutSessionCompleted( $session ): void {
        // One-time payments and subscriptions come through here
        if ( ( $session->mode ?? null ) === 'payment' ) {
            if ( !empty($session->payment_intent) ) {
                Payment::where('stripe_payment_intent_id', $session->payment_intent)
                       ->update([ 'status' => 'succeeded' ]);
            }
        }
        if ( ( $session->mode ?? null ) === 'subscription' && !empty($session->subscription) ) {
            $tenantId = (int) ( $session->metadata->tenant_id ?? 0 );
            $userId   = (int) ( $session->metadata->user_id ?? 0 );
            Subscription::updateOrCreate(
                [ 'tenant_id' => $tenantId, 'user_id' => $userId ],
                [
                    'stripe_subscription_id' => $session->subscription,
                    'stripe_price_id'        => $session->display_items[0]->plan->id ?? ( $session->line_items->data[0]->price->id ?? '' ),
                    'status'                 => 'active',
                    'cancel_at_period_end'   => false,
                ]
            );
        }
    }

    protected function onPaymentSucceeded( $paymentIntent ): void {
        Payment::where('stripe_payment_intent_id', $paymentIntent->id)
               ->update([ 'status' => 'succeeded' ]);
    }

    protected function onPaymentFailed( $paymentIntent ): void {
        Payment::where('stripe_payment_intent_id', $paymentIntent->id)
               ->update([ 'status' => 'failed' ]);
    }

    protected function onChargeRefunded( $charge ): void {
        if ( !empty($charge->payment_intent) ) {
            $amountRefunded = (int) ( $charge->amount_refunded ?? 0 );
            $payment        = Payment::where('stripe_payment_intent_id', $charge->payment_intent)->first();
            if ( $payment ) {
                $payment->status = $amountRefunded >= $payment->amount ? 'refunded' : 'partial_refund';
                $payment->type   = $amountRefunded >= $payment->amount ? 'refund' : 'partial_refund';
                $payment->save();
            }
        }
    }

    protected function onInvoicePaymentSucceeded( $invoice ): void {
        if ( !empty($invoice->subscription) ) {
            $periodEnd = $invoice->lines->data[0]->period->end ?? null;
            Subscription::where('stripe_subscription_id', $invoice->subscription)
                        ->update([
                            'status'             => 'active',
                            'current_period_end' => $periodEnd ? Carbon::createFromTimestamp($periodEnd) : null,
                        ]);
        }
    }

    protected function onInvoicePaymentFailed( $invoice ): void {
        if ( !empty($invoice->subscription) ) {
            Subscription::where('stripe_subscription_id', $invoice->subscription)
                        ->update([ 'status' => 'past_due' ]);
        }
    }

    protected function onSubscriptionEvent( $subscription ): void {
        $periodEnd = $subscription->current_period_end ?? null;
        Subscription::updateOrCreate(
            [ 'stripe_subscription_id' => $subscription->id ],
            [
                'tenant_id'            => (int) ( $subscription->metadata->tenant_id ?? 0 ),
                'user_id'              => (int) ( $subscription->metadata->user_id ?? 0 ),
                'stripe_price_id'      => $subscription->items->data[0]->price->id ?? '',
                'status'               => $subscription->status,
                'current_period_end'   => $periodEnd ? Carbon::createFromTimestamp($periodEnd) : null,
                'cancel_at_period_end' => (bool) ( $subscription->cancel_at_period_end ?? false ),
            ]
        );
    }
}
