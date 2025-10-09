<?php

declare(strict_types=1);

namespace App\Http\Controllers\Stripe;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\StripeWebhookLog;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Stripe\StripeTenantClient;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        $payload = $request->getContent();
        $sig = $request->header('Stripe-Signature', '');

        $tenantClient = new StripeTenantClient();
        $secret = $tenantClient->webhookSecret();
        if (!$secret) {
            return response(['message' => 'Missing webhook secret'], 400);
        }

        try {
            $event = Webhook::constructEvent($payload, $sig, $secret);
        } catch (\Throwable $e) {
            $this->logEvent('invalid', $payload, error: $e->getMessage());
            return response(['message' => 'Invalid signature'], 400);
        }

        $type = $event->type;
        $data = $event->data->object ?? null;
        $this->logEvent($type, $payload);

        try {
            match ($type) {
                'checkout.session.completed' => $this->onCheckoutSessionCompleted($data),
                'payment_intent.succeeded' => $this->onPaymentIntentSucceeded($data),
                'payment_intent.payment_failed' => $this->onPaymentIntentFailed($data),
                'charge.refunded' => $this->onChargeRefunded($data),
                'invoice.payment_succeeded' => $this->onInvoicePaymentSucceeded($data),
                'invoice.payment_failed' => $this->onInvoicePaymentFailed($data),
                'customer.subscription.created' => $this->onSubscriptionUpdated($data, created: true),
                'customer.subscription.updated' => $this->onSubscriptionUpdated($data),
                'customer.subscription.deleted' => $this->onSubscriptionDeleted($data),
                default => null,
            };
        } catch (\Throwable $e) {
            Log::error('Stripe webhook handler error', ['type' => $type, 'error' => $e->getMessage()]);
            $this->logEvent($type, $payload, status: 'error', error: $e->getMessage());
            return response(['message' => 'Error processing event'], 500);
        }

        return response(['received' => true], 200);
    }

    protected function onCheckoutSessionCompleted($session): void
    {
        $pi = $session->payment_intent ?? null;
        if (!$pi) return;
        $userId = $session->metadata->user_id ?? null;
        $amount = $session->amount_total ?? null;
        $currency = strtoupper($session->currency ?? 'DKK');

        if ($userId && $amount) {
            Payment::query()->updateOrCreate(
                ['tenant_id' => tenant()?->id, 'stripe_payment_intent_id' => $pi],
                [
                    'user_id' => (int)$userId,
                    'stripe_session_id' => $session->id,
                    'amount' => (int)$amount,
                    'currency' => $currency,
                    'status' => 'succeeded',
                    'type' => 'payment',
                ]
            );
        }
    }

    protected function onPaymentIntentSucceeded($pi): void
    {
        $userId = $pi->metadata->user_id ?? null;
        $amount = $pi->amount_received ?? $pi->amount ?? null;
        $currency = strtoupper($pi->currency ?? 'DKK');

        if ($userId && $amount) {
            Payment::query()->updateOrCreate(
                ['tenant_id' => tenant()?->id, 'stripe_payment_intent_id' => $pi->id],
                [
                    'user_id' => (int)$userId,
                    'amount' => (int)$amount,
                    'currency' => $currency,
                    'status' => 'succeeded',
                    'type' => 'payment',
                ]
            );
        }
    }

    protected function onPaymentIntentFailed($pi): void
    {
        Payment::query()->where('stripe_payment_intent_id', $pi->id)
            ->update(['status' => 'failed']);
    }

    protected function onChargeRefunded($charge): void
    {
        $pi = $charge->payment_intent ?? null;
        if (!$pi) return;
        $amount = $charge->amount_refunded ?? null;
        $currency = strtoupper($charge->currency ?? 'DKK');

        if ($amount) {
            Payment::query()->updateOrCreate(
                ['tenant_id' => tenant()?->id, 'stripe_payment_intent_id' => $pi, 'type' => $amount === ($charge->amount ?? 0) ? 'refund' : 'partial_refund'],
                [
                    'user_id' => (int)($charge->metadata->user_id ?? auth()->id()),
                    'amount' => (int)$amount,
                    'currency' => $currency,
                    'status' => 'refunded',
                ]
            );
        }
    }

    protected function onInvoicePaymentSucceeded($invoice): void
    {
        $subId = $invoice->subscription ?? null;
        if (!$subId) return;
        Subscription::query()->where('stripe_subscription_id', $subId)->update([
            'status' => 'active',
            'current_period_end' => now()->setTimestamp($invoice->lines->data[0]->period->end ?? time()),
        ]);
    }

    protected function onInvoicePaymentFailed($invoice): void
    {
        $subId = $invoice->subscription ?? null;
        if (!$subId) return;
        Subscription::query()->where('stripe_subscription_id', $subId)->update([
            'status' => 'past_due',
        ]);
    }

    protected function onSubscriptionUpdated($sub, bool $created = false): void
    {
        $userId = $sub->metadata->user_id ?? null;
        $priceId = $sub->items->data[0]->price->id ?? null;
        $status = $sub->status ?? null;
        $cancelAtPeriodEnd = (bool)($sub->cancel_at_period_end ?? false);
        $periodEnd = isset($sub->current_period_end) ? now()->setTimestamp($sub->current_period_end) : null;

        if ($priceId && $userId) {
            Subscription::query()->updateOrCreate(
                ['tenant_id' => tenant()?->id, 'stripe_subscription_id' => $sub->id],
                [
                    'user_id' => (int)$userId,
                    'stripe_price_id' => $priceId,
                    'status' => $status,
                    'current_period_end' => $periodEnd,
                    'cancel_at_period_end' => $cancelAtPeriodEnd,
                ]
            );
        }
    }

    protected function onSubscriptionDeleted($sub): void
    {
        Subscription::query()->where('stripe_subscription_id', $sub->id)
            ->update(['status' => 'canceled']);
    }

    protected function logEvent(string $type, string $payload, ?string $status = 'received', ?string $error = null): void
    {
        StripeWebhookLog::create([
            'tenant_id' => tenant()?->id,
            'event_type' => $type,
            'payload' => json_decode($payload, true) ?: [],
            'processed_at' => now(),
            'status' => $status,
            'error' => $error,
        ]);
    }
}
