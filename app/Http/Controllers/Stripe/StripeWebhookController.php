<?php

declare(strict_types=1);

namespace App\Http\Controllers\Stripe;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\ProcessedStripeEvent;
use App\Models\StripeWebhookLog;
use App\Models\Subscription;
use App\Services\Stripe\StripeTenantClient;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        $payload = $request->getContent();
        $sig = $request->header('Stripe-Signature', '');
        // Platform events: no Stripe-Account header, tenant is null. Connected-account events: Stripe-Account header identifies tenant.
        $connected = $request->header('Stripe-Account');
        $tenant = $connected ? \App\Models\Tenant::where('stripe_connect_account_id', $connected)->first() : null;

        $tenantClient = new StripeTenantClient($tenant);
        // Bind tenant context for downstream model scoping and logging
        if ($tenant) {
            session(['tenant_id' => $tenant->id]);
            app()->instance('tenant', $tenant);
        }

        $secret = $tenantClient->webhookSecret();
        if (! $secret) {
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

        // Idempotency: skip if already processed (Stripe retries webhooks)
        $processed = ProcessedStripeEvent::firstOrCreate(['event_id' => $event->id]);
        if (! $processed->wasRecentlyCreated) {
            return response(['received' => true], 200);
        }

        $this->logEvent($type, $payload);

        try {
            DB::transaction(function () use ($type, $data, $connected) {
                // Platform events (no Stripe-Account): AI Coach billing
                if (! $connected) {
                    match ($type) {
                        'checkout.session.completed' => $this->onPlatformCheckoutSessionCompleted($data),
                        'customer.subscription.updated' => $this->onPlatformSubscriptionUpdated($data),
                        'customer.subscription.deleted' => $this->onPlatformSubscriptionDeleted($data),
                        default => null,
                    };

                    return;
                }
                // Connect events (tenant-scoped)
                match ($type) {
                    'account.updated' => $this->onAccountUpdated($data),
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
            });
        } catch (\Throwable $e) {
            Log::channel('stripe')->error('Stripe webhook handler error', [
                'type' => $type,
                'event_id' => $event->id ?? null,
                'tenant_id' => tenant()?->id,
                'error' => $e->getMessage(),
            ]);
            $this->logEvent($type, $payload, status: 'error', error: $e->getMessage());

            return response(['message' => 'Error processing event'], 500);
        }

        return response(['received' => true], 200);
    }

    protected function onCheckoutSessionCompleted($session): void
    {
        if (! $session) {
            return;
        }
        $pi = $session->payment_intent ?? null;
        if (! $pi) {
            return;
        }
        $metadata = $session->metadata ?? null;
        $userId = $metadata->user_id ?? null;
        $planType = $metadata->plan_type ?? null;
        $credits = (int) ($metadata->credits ?? 0);
        $amount = $session->amount_total ?? null;
        $currency = strtoupper($session->currency ?? 'DKK');

        if ($userId && $amount) {
            Payment::query()->updateOrCreate(
                ['tenant_id' => tenant()?->id, 'stripe_payment_intent_id' => $pi],
                [
                    'user_id' => (int) $userId,
                    'stripe_session_id' => $session->id,
                    'amount' => (int) $amount,
                    'currency' => $currency,
                    'status' => 'succeeded',
                    'type' => 'payment',
                ]
            );
        }

        // Grant one-off credits when applicable
        if ($userId && $planType === 'one_off' && $credits > 0) {
            $sub = Subscription::query()->updateOrCreate(
                ['tenant_id' => tenant()?->id, 'user_id' => (int) $userId],
                ['plan_type' => 'one_off', 'status' => 'one_off']
            );
            $sub->increment('credits_remaining', $credits);
        }
    }

    protected function onPaymentIntentSucceeded($pi): void
    {
        if (! $pi) {
            return;
        }
        $metadata = $pi->metadata ?? null;
        $userId = $metadata->user_id ?? null;
        $planType = $metadata->plan_type ?? null;
        $credits = (int) ($metadata->credits ?? 0);
        $amount = $pi->amount_received ?? $pi->amount ?? null;
        $currency = strtoupper($pi->currency ?? 'DKK');

        if ($userId && $amount) {
            Payment::query()->updateOrCreate(
                ['tenant_id' => tenant()?->id, 'stripe_payment_intent_id' => $pi->id],
                [
                    'user_id' => (int) $userId,
                    'amount' => (int) $amount,
                    'currency' => $currency,
                    'status' => 'succeeded',
                    'type' => 'payment',
                ]
            );
        }

        // Grant one-off credits as a fallback (if not already handled in checkout.session.completed)
        if ($userId && $planType === 'one_off' && $credits > 0) {
            $sub = Subscription::query()->updateOrCreate(
                ['tenant_id' => tenant()?->id, 'user_id' => (int) $userId],
                ['plan_type' => 'one_off', 'status' => 'one_off']
            );
            $sub->increment('credits_remaining', $credits);
        }
    }

    protected function onPaymentIntentFailed($pi): void
    {
        if (! $pi || empty($pi->id)) {
            return;
        }
        Payment::query()->where('stripe_payment_intent_id', $pi->id)
            ->update(['status' => 'failed']);
    }

    protected function onChargeRefunded($charge): void
    {
        if (! $charge) {
            return;
        }
        $pi = $charge->payment_intent ?? null;
        if (! $pi) {
            return;
        }
        $piId = is_string($pi) ? $pi : ($pi->id ?? null);
        if (! $piId) {
            return;
        }
        $amount = $charge->amount_refunded ?? null;
        $currency = strtoupper($charge->currency ?? 'DKK');
        $chargeMetadata = $charge->metadata ?? null;
        $userId = (int) ($chargeMetadata->user_id ?? auth()->id() ?? 0);

        if ($amount) {
            $refundType = $amount === ($charge->amount ?? 0) ? 'refund' : 'partial_refund';
            Payment::query()->updateOrCreate(
                ['tenant_id' => tenant()?->id, 'stripe_payment_intent_id' => $piId, 'type' => $refundType],
                [
                    'user_id' => $userId,
                    'amount' => (int) $amount,
                    'currency' => $currency,
                    'status' => 'refunded',
                ]
            );
        }
    }

    protected function onInvoicePaymentSucceeded($invoice): void
    {
        if (! $invoice) {
            return;
        }
        $subId = $invoice->subscription ?? null;
        if (! $subId) {
            return;
        }
        $lines = $invoice->lines ?? null;
        $data = $lines?->data ?? [];
        $firstLine = $data[0] ?? null;
        $period = $firstLine?->period ?? null;
        $periodEnd = $period?->end ?? time();
        $periodEnd = is_numeric($periodEnd) ? (int) $periodEnd : time();

        Subscription::query()->where('stripe_subscription_id', $subId)->update([
            'status' => 'active',
            'current_period_end' => now()->setTimestamp($periodEnd),
        ]);
    }

    protected function onInvoicePaymentFailed($invoice): void
    {
        $subId = $invoice->subscription ?? null;
        if (! $subId) {
            return;
        }
        $subscription = Subscription::where('stripe_subscription_id', $subId)->first();
        if ($subscription) {
            $subscription->update(['status' => 'past_due']);

            if ($subscription->user) {
                $plan = \App\Models\Plan::where('stripe_price_id', $subscription->stripe_price_id)->first();
                event(new \App\Events\PaymentFailed($subscription->user, $plan));
            }
        }
    }

    protected function onSubscriptionUpdated($sub, bool $created = false): void
    {
        if (! $sub) {
            return;
        }
        $metadata = $sub->metadata ?? null;
        $userId = $metadata?->user_id ?? null;
        $items = $sub->items ?? null;
        $itemsData = $items?->data ?? [];
        $firstItem = $itemsData[0] ?? null;
        $price = $firstItem?->price ?? null;
        $priceId = $price?->id ?? null;
        $status = $sub->status ?? null;
        $cancelAtPeriodEnd = (bool) ($sub->cancel_at_period_end ?? false);
        $periodEnd = isset($sub->current_period_end) ? now()->setTimestamp($sub->current_period_end) : null;

        if ($priceId && $userId) {
            $subscription = Subscription::query()->updateOrCreate(
                ['tenant_id' => tenant()?->id, 'stripe_subscription_id' => $sub->id],
                [
                    'user_id' => (int) $userId,
                    'stripe_price_id' => $priceId,
                    'status' => $status,
                    'current_period_end' => $periodEnd,
                    'cancel_at_period_end' => $cancelAtPeriodEnd,
                ]
            );

            if ($created && $subscription->user) {
                $plan = \App\Models\Plan::where('stripe_price_id', $priceId)->first();
                event(new \App\Events\SubscriptionCreated($subscription->user, $plan));
            }
        }
    }

    protected function onSubscriptionDeleted($sub): void
    {
        if (! $sub || empty($sub->id)) {
            return;
        }
        Subscription::query()->where('stripe_subscription_id', $sub->id)
            ->update(['status' => 'canceled']);
    }

    protected function onPlatformCheckoutSessionCompleted($session): void
    {
        if (! $session || ($session->metadata->type ?? null) !== 'ai_coach') {
            return;
        }
        $tenantId = $session->metadata->tenant_id ?? null;
        if (! $tenantId) {
            return;
        }
        $subscriptionId = $session->subscription ?? null;
        if (! $subscriptionId) {
            return;
        }
        $tenant = \App\Models\Tenant::find($tenantId);
        if (! $tenant) {
            return;
        }
        $subId = is_string($subscriptionId) ? $subscriptionId : $subscriptionId->id;
        $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
        $sub = $stripe->subscriptions->retrieve($subId);
        $items = $sub->items ?? null;
        $itemsData = $items?->data ?? [];
        $firstItem = $itemsData[0] ?? null;
        $price = $firstItem?->price ?? null;
        $priceId = $price?->id ?? null;

        $tenant->update([
            'ai_coach_enabled_at' => now(),
            'ai_coach_stripe_subscription_id' => $subId,
            'ai_coach_stripe_price_id' => $priceId,
        ]);
    }

    protected function onPlatformSubscriptionUpdated($sub): void
    {
        if (! $sub || empty($sub->id)) {
            return;
        }
        $tenant = \App\Models\Tenant::where('ai_coach_stripe_subscription_id', $sub->id)->first();
        if (! $tenant) {
            return;
        }
        $status = $sub->status ?? null;
        if ($status === 'canceled' || $status === 'unpaid') {
            $tenant->update([
                'ai_coach_enabled_at' => null,
                'ai_coach_stripe_subscription_id' => null,
                'ai_coach_stripe_price_id' => null,
            ]);
        }
    }

    protected function onPlatformSubscriptionDeleted($sub): void
    {
        if (! $sub || empty($sub->id)) {
            return;
        }
        \App\Models\Tenant::where('ai_coach_stripe_subscription_id', $sub->id)->update([
            'ai_coach_enabled_at' => null,
            'ai_coach_stripe_subscription_id' => null,
            'ai_coach_stripe_price_id' => null,
        ]);
    }

    protected function onAccountUpdated($account): void
    {
        if (! $account || empty($account->id)) {
            return;
        }
        $tenant = \App\Models\Tenant::where('stripe_connect_account_id', $account->id)->first();
        if ($tenant) {
            $tenant->update([
                'stripe_connect_charges_enabled' => (bool) ($account->charges_enabled ?? false),
                'stripe_connect_payouts_enabled' => (bool) ($account->payouts_enabled ?? false),
                'stripe_connect_onboarded' => (bool) ($account->details_submitted ?? false),
            ]);
            Log::channel('stripe')->info('account.updated handled', [
                'tenant_id' => $tenant->id,
                'account_id' => $account->id,
                'charges_enabled' => $account->charges_enabled ?? null,
                'payouts_enabled' => $account->payouts_enabled ?? null,
                'details_submitted' => $account->details_submitted ?? null,
            ]);
        }
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
