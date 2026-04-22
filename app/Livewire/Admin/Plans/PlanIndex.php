<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Plans;

use App\Exports\PlansExport;
use App\Models\Plan;
use App\Traits\WithSorting;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class PlanIndex extends Component
{
    use AuthorizesRequests, WithPagination, WithSorting;

    public string $search = '';

    public function mount(): void
    {
        $this->sortField = 'name';
        $this->sortDirection = 'asc';
    }

    public function render(): View
    {
        $query = Plan::query()
            ->withCount(['subscriptions as subscribers_count' => function ($q) {
                $q->where('status', 'active');
            }]);
        $query = $this->applyFilters($query);
        $plans = $this->applySorting($query)->paginate(10);

        return view('livewire.admin.plans.index', [
            'plans' => $plans,
        ]);
    }

    protected function applyFilters($query)
    {
        return $query->when($this->search !== '', function ($q) {
            $q->where(function ($qq) {
                $qq->where('name', 'like', "%{$this->search}%")
                    ->orWhere('stripe_price_id', 'like', "%{$this->search}%");
            });
        });
    }

    public function export()
    {
        $query = Plan::query()
            ->withCount(['subscriptions as subscribers_count' => function ($q) {
                $q->where('status', 'active');
            }]);
        $query = $this->applyFilters($query);

        return Excel::download(new PlansExport($query), 'plans.xlsx');
    }

    public function delete(int $planId): void
    {
        $plan = Plan::query()->find($planId);
        if (! $plan) {
            return;
        }
        try {
            $this->authorize('delete', $plan);
        } catch (AuthorizationException $e) {
            session()->flash('status', __('You are not authorized to delete this plan.'));

            return;
        }

        // Prevent deletion (even soft delete) when there are active subscriptions on this plan
        // We treat both 'active' and 'trialing' as blocking statuses
        $hasActiveSubs = $plan->subscriptions()
            ->whereIn('status', ['active', 'trialing'])
            ->exists();
        if ($hasActiveSubs) {
            session()->flash('status', __('Plan cannot be deleted because it has active subscriptions.'));

            return;
        }

        // Soft delete the plan
        $plan->delete();
        session()->flash('status', __('Plan deleted.'));

        // Reset to first page if current page becomes empty
        if ($this->page > 1 && $this->plans()->isEmpty()) {
            $this->resetPage();
        }
    }
}
