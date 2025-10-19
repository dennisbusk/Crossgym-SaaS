<?php

declare(strict_types=1);

namespace App\Livewire\Admin\StripeWebhookLogs;

use App\Models\StripeWebhookLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class StripeWebhookLogIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function render(): View
    {
        return view('livewire.admin.stripe-webhook-logs.index', [
            'logs' => $this->logs(),
        ]);
    }

    protected function logs(): LengthAwarePaginator
    {
        return StripeWebhookLog::query()
            ->when($this->search !== '', function ($q) {
                $q->where(function ($qq) {
                    $qq->where('event_type', 'like', "%{$this->search}%")
                       ->orWhere('status', 'like', "%{$this->search}%")
                       ->orWhere('error', 'like', "%{$this->search}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(10);
    }
}
