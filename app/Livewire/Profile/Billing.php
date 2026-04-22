<?php

declare(strict_types=1);

namespace App\Livewire\Profile;

use App\Helpers\StripeHelper;
use App\Models\Plan;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Billing extends Component
{
    public array $invoices = [];

    public ?array $upcoming = null;

    public $plans = [];

    public $currentPlanId = null;

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $this->plans = Plan::where('tenant_id', $user->tenant_id)->get();
        $this->currentPlanId = $user->subscription?->stripe_price_id;

        // If customer is not linked yet, do not fail – show empty lists.
        $customerId = $user->stripe_customer_id;
        if (! $customerId) {
            $this->invoices = [];
            $this->upcoming = null;

            return;
        }

        $client = StripeHelper::getStripeClient();
        $opts = (new \App\Services\Stripe\StripeTenantClient)->options();

        // Fetch latest invoices (payments history)
        $invoices = $client->invoices->all([
            'customer' => $customerId,
            'limit' => 20,
        ], $opts);

        $this->invoices = collect($invoices->data ?? [])
            ->map(function ($inv) {
                return [
                    'id' => $inv->id,
                    'number' => $inv->number,
                    'status' => $inv->status,
                    'amount_paid' => ($inv->amount_paid ?? 0) / 100,
                    'currency' => strtoupper($inv->currency ?? 'DKK'),
                    'created' => isset($inv->created) ? date('Y-m-d H:i', (int) $inv->created) : null,
                    'pdf' => $inv->invoice_pdf ?? null,
                ];
            })
            ->all();

        // Fetch upcoming invoice (next payment)
        try {
            $upcoming = $client->invoices->upcoming([
                'customer' => $customerId,
            ], $opts);

            if ($upcoming) {
                $this->upcoming = [
                    'amount_due' => ($upcoming->amount_due ?? 0) / 100,
                    'currency' => strtoupper($upcoming->currency ?? 'DKK'),
                    'next_payment_attempt' => isset($upcoming->next_payment_attempt) ? date('Y-m-d H:i', (int) $upcoming->next_payment_attempt) : null,
                ];
            }
        } catch (\Throwable $e) {
            // No upcoming invoice or Stripe error; keep null
            $this->upcoming = null;
        }
    }

    public function subscribe($planId)
    {
        $tenant = tenant();
        if (! $tenant->allow_member_billing_management) {
            session()->flash('error', __('You cannot manage your subscription. Please contact your administrator.'));

            return;
        }

        $user = Auth::user();
        $plan = Plan::findOrFail($planId);

        $stripe = \App\Services\Stripe\StripeService::forTenant($tenant);

        $params = [
            'price_id' => $plan->stripe_price_id,
            'success_url' => route('profile.billing', ['success' => 1]),
            'cancel_url' => route('profile.billing'),
            'user_id' => $user->id,
            'metadata' => [
                'user_id' => $user->id,
            ],
        ];

        if ($user->stripe_customer_id) {
            $params['customer_id'] = $user->stripe_customer_id;
        } else {
            $params['customer_email'] = $user->email;
        }

        $session = $stripe->createSubscriptionCheckout($params);

        return redirect($session['url']);
    }

    public function render()
    {
        return view('livewire.profile.billing');
    }
}
