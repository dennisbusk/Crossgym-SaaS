<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Payments;

use App\Models\Payment;
use App\Traits\WithSorting;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class PaymentIndex extends Component
{
    use WithPagination;
    use WithSorting;

    public string $search = '';

    public string $status = '';

    public bool $showManualPaymentModal = false;

    public $manualUserId;

    public $manualAmount;

    public $manualNotes;

    public function render(): View
    {
        $query = Payment::query()->with('user');
        $query = $this->applyFilters($query);
        $payments = $this->applySorting($query)->paginate(10);

        return view('livewire.admin.payments.index', [
            'payments' => $payments,
            'users' => \App\Models\User::all(),
        ]);
    }

    public function export()
    {
        $this->authorize('viewAny', \App\Models\Payment::class);

        $query = Payment::query()->with('user');
        $query = $this->applyFilters($query);

        return Excel::download(new \App\Exports\PaymentsExport($query), 'payments.xlsx');
    }

    protected function applyFilters($query)
    {
        return $query->when($this->search !== '', function ($q) {
            $q->where(function ($qq) {
                $qq->where('stripe_payment_intent_id', 'like', "%{$this->search}%")
                    ->orWhere('stripe_session_id', 'like', "%{$this->search}%")
                    ->orWhere('status', 'like', "%{$this->search}%")
                    ->orWhere('currency', 'like', "%{$this->search}%")
                    ->orWhereHas('user', function ($uq) {
                        $uq->where('name', 'like', "%{$this->search}%");
                    });
            });
        })
            ->when($this->status !== '', function ($q) {
                $q->where('status', $this->status);
            });
    }

    public function refund($paymentId, $amount = null)
    {
        $payment = Payment::findOrFail($paymentId);

        // If it's a Stripe payment, we should ideally call Stripe API
        // For now, let's just record it in the DB
        $refundAmount = $amount ?? ($payment->amount - $payment->refunded_amount);

        $payment->refunded_amount += $refundAmount;
        $payment->status = $payment->refunded_amount >= $payment->amount ? 'refunded' : 'partially_refunded';
        $payment->save();

        session()->flash('status', __('Refund registered.'));
    }

    public function openManualPaymentModal()
    {
        $this->showManualPaymentModal = true;
    }

    public function saveManualPayment()
    {
        $this->validate([
            'manualUserId' => 'required|exists:users,id',
            'manualAmount' => 'required|numeric|min:0',
        ]);

        Payment::create([
            'tenant_id' => auth()->user()->tenant_id,
            'user_id' => $this->manualUserId,
            'amount' => $this->manualAmount * 100, // stored in cents
            'currency' => 'DKK',
            'status' => 'succeeded',
            'type' => 'manual',
            'manual_payment_by' => auth()->id(),
            'notes' => $this->manualNotes,
        ]);

        $this->showManualPaymentModal = false;
        $this->reset(['manualUserId', 'manualAmount', 'manualNotes']);
        session()->flash('status', __('Manual payment registered.'));
    }
}
