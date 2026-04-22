<?php

declare(strict_types=1);

namespace App\Http\Controllers\Stripe;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\Stripe\StripeConnectService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StripeConnectController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected StripeConnectService $service = new StripeConnectService
    ) {}

    // Legacy entry point (OAuth authorize). Redirect to new start route.
    public function connect(Request $request): RedirectResponse
    {
        return redirect()->route('stripe.connect.start');
    }

    // New: Start hosted onboarding by creating an account link
    public function start(Request $request): RedirectResponse
    {
        $tenant = $this->currentTenant();
        $this->authorize('update', $tenant);

        $url = $this->service->createAccountLink($tenant);

        return redirect()->away($url);
    }

    // New: Refresh onboarding session (if expired)
    public function refresh(Request $request): RedirectResponse
    {
        $tenant = $this->currentTenant();
        $this->authorize('update', $tenant);
        $url = $this->service->createAccountLink($tenant);

        return redirect()->away($url);
    }

    // New: Return URL after hosted onboarding completes
    public function return(Request $request): RedirectResponse
    {
        $tenant = $this->currentTenant();
        $this->authorize('update', $tenant);
        if ($tenant->stripe_connect_account_id) {
            $this->service->updateTenantAccountStatus($tenant->stripe_connect_account_id);
        }

        return redirect()->route('dashboard')->with('status', __('Stripe er forbundet!'));
    }

    // Optional: OAuth callback support for Standard accounts
    public function callback(Request $request): RedirectResponse
    {
        $tenant = $this->currentTenant();
        $this->authorize('update', $tenant);

        if ($request->filled('code')) {
            $this->service->handleOAuthCallback($request);

            return redirect()->route('dashboard')->with('status', __('Connected to Stripe.'));
        }

        return redirect()->route('dashboard')->with('status', __('Stripe connection failed.'));
    }

    protected function currentTenant(): Tenant
    {
        /** @var Authenticatable&\App\Models\User $user */
        $user = Auth::user();

        return $user->tenant;
    }
}
