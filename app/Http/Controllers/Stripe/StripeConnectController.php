<?php

declare(strict_types=1);

namespace App\Http\Controllers\Stripe;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class StripeConnectController extends Controller
{
    public function connect(Request $request): RedirectResponse
    {
        $this->authorize('update', $this->currentTenant());

        $clientId = config('services.stripe.connect_client_id');
        $base = 'https://connect.stripe.com/oauth/authorize';
        $params = http_build_query([
            'response_type' => 'code',
            'client_id' => $clientId,
            'scope' => 'read_write',
        ]);

        return redirect($base . '?' . $params);
    }

    public function callback(Request $request): RedirectResponse
    {
        $tenant = $this->currentTenant();
        $this->authorize('update', $tenant);

        $code = $request->string('code');
        abort_if(!$code, 400, __('Missing code'));

        $resp = Http::asForm()->post('https://connect.stripe.com/oauth/token', [
            'client_secret' => config('services.stripe.secret'),
            'code' => $code,
            'grant_type' => 'authorization_code',
        ]);

        if (!$resp->ok()) {
            return redirect()->back()->with('status', __('Stripe connection failed.'));
        }

        $data = $resp->json();

        $tenant->update([
            'stripe_connect_account_id' => $data['stripe_user_id'] ?? null,
            'stripe_connect_refresh_token' => $data['refresh_token'] ?? null,
            'stripe_connect_access_token' => $data['access_token'] ?? null,
        ]);

        return redirect()->route('admin.dashboard')->with('status', __('Connected to Stripe.'));
    }

    protected function currentTenant(): Tenant
    {
        // assuming user belongs to a tenant
        /** @var Authenticatable&\App\Models\User $user */
        $user = Auth::user();
        return $user->tenant;
    }
}
