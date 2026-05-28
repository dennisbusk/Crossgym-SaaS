<?php

namespace App\Jobs;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Stripe\Charge;
use Stripe\Stripe;

class FetchStripePaymentIntent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $paymentId,
        public string $chargeId
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
            $charge = Charge::retrieve($this->chargeId);
            $paymentIntentId = $charge->payment_intent;

            if ($paymentIntentId) {
                Payment::where('id', $this->paymentId)->update([
                    'stripe_payment_intent_id' => $paymentIntentId,
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Kunne ikke hente Payment Intent for Charge {$this->chargeId}: ".$e->getMessage());
        }
    }
}
