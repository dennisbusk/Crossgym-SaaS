<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PlanUsersExport implements FromCollection, WithHeadings
{
    public function __construct(
        protected Plan $plan,
        protected ?string $search = null,
    ) {}

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function collection(): Collection
    {
        $tenantId = tenant()?->id;

        $query = User::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->whereHas('subscription', function ($q) {
                $q->where('plan_type', 'subscription')
                    ->whereIn('status', ['active', 'trialing'])
                    ->where('stripe_price_id', $this->plan->stripe_price_id);
            })
            ->with(['subscription' => function ($q) {
                $q->select(['id', 'user_id', 'status']);
            }])
            ->orderBy('name');

        if (! empty($this->search)) {
            $s = '%'.str_replace(['%', '_'], ['\\%', '\\_'], (string) $this->search).'%';
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', $s)
                    ->orWhere('email', 'like', $s);
            });
        }

        return $query->get(['id', 'name', 'email'])->map(function (User $u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'subscription_status' => (string) ($u->subscription->status ?? ''),
            ];
        });
    }

    public function headings(): array
    {
        return [
            __('ID'),
            __('Name'),
            __('Email'),
            __('Subscription status'),
        ];
    }
}
