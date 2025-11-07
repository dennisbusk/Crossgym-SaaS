<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Tenants;

use App\Models\Payment;
use App\Models\Tenant;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class TenantShow extends Component
{
    use AuthorizesRequests;

    public Tenant $tenant;

    public function mount(Tenant $tenant): void
    {
        $this->authorize('view', $tenant);
        $this->tenant = $tenant->load('subscriptionOption');
    }

    public function render()
    {
        $startOfYear = now()->startOfYear();

        $paymentsQuery = Payment::query()
            ->where('tenant_id', $this->tenant->id)
            ->where('status', 'succeeded')
            ->where('type', 'payment')
            ->where('created_at', '>=', $startOfYear);

        $transactionsCountYtd = (clone $paymentsQuery)->count('id');
        $tenantEarningsYtdMinor = (int) (clone $paymentsQuery)->sum('amount');

        $platformEarningsYtdMinor = null;
        $subscription = $this->tenant->subscriptionOption;
        if ($subscription && $subscription->type === 'transaction_fee') {
            // value is percentage like 0.5 meaning 0.5%
            $platformEarningsYtdMinor = (int) round($tenantEarningsYtdMinor * ((float) $subscription->value) / 100.0);
        }

        return view('livewire.admin.tenants.show', [
            'tenant' => $this->tenant,
            'transactionsCountYtd' => $transactionsCountYtd,
            'tenantEarningsYtdMinor' => $tenantEarningsYtdMinor,
            'platformEarningsYtdMinor' => $platformEarningsYtdMinor,
        ]);
    }
}
