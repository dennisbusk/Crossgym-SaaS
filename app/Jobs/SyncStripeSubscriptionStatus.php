<?php

namespace App\Jobs;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Subscription as StripeSubscription;

class SyncStripeSubscriptionStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $subscriptionId,
        public string $stripeSubscriptionId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $secret = config('services.stripe.secret');
        if (! $secret) {
            Log::error('Stripe secret key not configured.');

            return;
        }

        Stripe::setApiKey($secret);

        try {
            $stripeSub = StripeSubscription::retrieve($this->stripeSubscriptionId);

            Subscription::where('id', $this->subscriptionId)->update([
                'status' => $stripeSub->status,
                'current_period_end' => date('Y-m-d H:i:s', $stripeSub->current_period_end),
                'cancel_at_period_end' => (bool) $stripeSub->cancel_at_period_end,
            ]);
        } catch (\Exception $e) {
            Log::error("Kunne ikke synkronisere Stripe status for Subscription {$this->stripeSubscriptionId}: ".$e->getMessage());
        }
    }
}
