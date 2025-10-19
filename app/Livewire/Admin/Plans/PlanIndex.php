<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Plans;

use App\Exports\PlansExport;
use App\Models\Plan;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class PlanIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function render(): View
    {
        $plans = $this->plans();

        return view('livewire.admin.plans.index', [
            'plans' => $plans,
        ]);
    }

    /**
     * @return LengthAwarePaginator
     */
    protected function plans(): LengthAwarePaginator
    {
        return Plan::query()
            ->when($this->search !== '', function ($q) {
                $q->where(function ($qq) {
                    $qq->where('name', 'like', "%{$this->search}%")
                        ->orWhere('stripe_price_id', 'like', "%{$this->search}%");
                });
            })
            ->withCount(['subscriptions as subscribers_count' => function ($q) {
                $q->where('status', 'active');
            }])
            ->orderBy('name')
            ->paginate(10);
    }

    public function export()
    {
        $data = Plan::query()
            ->withCount(['subscriptions as subscribers_count' => function ($q) {
                $q->where('status', 'active');
            }])
            ->orderBy('name')
            ->get(['id', 'name', 'amount', 'currency', 'interval', 'stripe_price_id']);

        return Excel::download(new PlansExport($data), 'plans.xlsx');
    }
}
