<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Subscriptions;

use App\Exports\SubscriptionsExport;
use App\Models\Subscription;
use App\Traits\WithSorting;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class SubscriptionIndex extends Component
{
    use WithPagination;
    use WithSorting;

    public string $search = '';

    public function render(): View
    {
        return view('livewire.admin.subscriptions.index', [
            'subscriptions' => $this->subscriptions(),
        ]);
    }

    public function export()
    {
        $query = Subscription::query()
            ->with(['user', 'plan'])
            ->when($this->search !== '', function ($q) {
                $q->where(function ($qq) {
                    $qq->where('stripe_subscription_id', 'like', "%{$this->search}%")
                        ->orWhere('stripe_price_id', 'like', "%{$this->search}%")
                        ->orWhere('status', 'like', "%{$this->search}%");
                });
            });

        return Excel::download(new SubscriptionsExport($query), 'subscriptions.xlsx');
    }

    protected function subscriptions(): LengthAwarePaginator
    {
        $subscriptions = Subscription::query()
            ->with(['user', 'plan'])
            ->when($this->search !== '', function ($q) {
                $q->where(function ($qq) {
                    $qq->where('stripe_subscription_id', 'like', "%{$this->search}%")
                        ->orWhere('stripe_price_id', 'like', "%{$this->search}%")
                        ->orWhere('status', 'like', "%{$this->search}%");
                });
            });

        return $this->applySorting($subscriptions)->paginate(10);
    }
}
