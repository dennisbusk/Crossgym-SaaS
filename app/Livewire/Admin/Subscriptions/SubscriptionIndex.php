<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Subscriptions;

use App\Models\Subscription;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class SubscriptionIndex extends Component
{
    use WithPagination;

    public string $search = '';

    #[Layout('components.layouts.app.sidebar')]
    public function render(): View
    {
        return view('livewire.admin.subscriptions.index', [
            'subscriptions' => $this->subscriptions(),
        ]);
    }

    protected function subscriptions(): LengthAwarePaginator
    {
        return Subscription::query()
            ->with(['user'])
            ->when($this->search !== '', function ($q) {
                $q->where(function ($qq) {
                    $qq->where('stripe_subscription_id', 'like', "%{$this->search}%")
                       ->orWhere('stripe_price_id', 'like', "%{$this->search}%")
                       ->orWhere('status', 'like', "%{$this->search}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(10);
    }
}
