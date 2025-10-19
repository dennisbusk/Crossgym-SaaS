<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Payments;

use App\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class PaymentIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function render(): View
    {
        return view('livewire.admin.payments.index', [
            'payments' => $this->payments(),
        ]);
    }

    protected function payments(): LengthAwarePaginator
    {
        return Payment::query()
            ->with('user')
            ->when($this->search !== '', function ($q) {
                $q->where(function ($qq) {
                    $qq->where('stripe_payment_intent_id', 'like', "%{$this->search}%")
                       ->orWhere('stripe_session_id', 'like', "%{$this->search}%")
                       ->orWhere('status', 'like', "%{$this->search}%")
                       ->orWhere('currency', 'like', "%{$this->search}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(10);
    }
}
