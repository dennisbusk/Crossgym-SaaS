<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\SubscriptionOption;
use App\Models\Tenant;
use App\Services\Stripe\StripeService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TenantChooseSubscription extends Component
{
    public ?int $selected = null;

    public function mount(): void
    {
        $tenant = tenant();
        if ($tenant && $tenant->subscription_option_id) {
            $this->selected = (int) $tenant->subscription_option_id;
        }
    }

    public function render(): View
    {
        return view('livewire.admin.tenants.choose-subscription', [
            'options' => SubscriptionOption::query()->where('active', true)->orderBy('id')->get(),
        ]);
    }

    public function select(int $optionId): void
    {
        $this->selected = $optionId;
    }

    public function confirm(StripeService $stripe)
    {
        $tenant = tenant();
        abort_unless(Auth::check() && $tenant, 403);

        $option = SubscriptionOption::query()->where('active', true)->findOrFail((int) $this->selected);

        /** @var Tenant $tenant */
        $tenant->forceFill(['subscription_option_id' => $option->id])->save();

        if ($tenant->stripe_connect_account_id) {
            $stripe->updateConnectedAccountMetadata($tenant, [
                'subscription_option_id' => (string) $option->id,
                'subscription_option_type' => $option->type,
                'subscription_option_value' => (string) $option->value,
            ]);
        }

        session()->flash('banner', __('Subscription option saved.'));
        session()->flash('bannerStyle', 'success');

        // Redirect back to the same page so testing can assert flashed session keys
        return redirect()->route('tenant.subscription');
    }
}
