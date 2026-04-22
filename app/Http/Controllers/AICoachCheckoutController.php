<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Services\Stripe\AICoachBillingService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AICoachCheckoutController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected AICoachBillingService $billingService = new AICoachBillingService
    ) {}

    public function checkout(Request $request): RedirectResponse
    {
        $tenant = $this->currentTenant();
        $this->authorize('update', $tenant);

        $validated = $request->validate([
            'interval' => ['required', Rule::in(['monthly', 'yearly'])],
        ]);

        $priceId = $this->billingService->getPriceIdForInterval($validated['interval']);

        if (! $priceId) {
            return redirect()->route('ai-coach-settings.index')
                ->with('error', __('AI Coach pricing is not configured. Please contact support.'));
        }

        $user = Auth::user();
        $successUrl = route('ai-coach.checkout.success', ['session_id' => '{CHECKOUT_SESSION_ID}']);
        $cancelUrl = route('ai-coach-settings.index');

        $session = $this->billingService->createCheckoutSession(
            $tenant,
            $priceId,
            $user->email,
            $successUrl,
            $cancelUrl
        );

        return redirect()->away($session->url);
    }

    public function success(Request $request): RedirectResponse
    {
        $tenant = $this->currentTenant();
        $this->authorize('update', $tenant);

        $sessionId = $request->query('session_id');
        if (! $sessionId) {
            return redirect()->route('ai-coach-settings.index')
                ->with('error', __('Checkout session not found.'));
        }

        return redirect()->route('ai-coach-settings.index')
            ->with('status', __('AI Coach has been activated! Add your API key in the settings below to start using it.'));
    }

    public function cancel(): RedirectResponse
    {
        return redirect()->route('ai-coach-settings.index');
    }

    protected function currentTenant(): Tenant
    {
        /** @var Authenticatable&\App\Models\User $user */
        $user = Auth::user();

        return $user->tenant;
    }
}
