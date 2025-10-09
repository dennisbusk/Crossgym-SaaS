<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Exports\DashboardStatsExport;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class Dashboard extends Component
{
    public int $totalTransactions = 0;
    public int $totalRevenueDkk = 0; // in øre (cents)
    public int $totalBookingsActive = 0;
    public int $totalBookingsCompleted = 0;
    /** @var array<string,int> */
    public array $subscribersByPlan = [];

    public function mount(): void
    {
        // TODO: Pull real stats from local DB and/or Stripe APIs.
        $this->totalTransactions = 0;
        $this->totalRevenueDkk = 0;
        $this->totalBookingsActive = 0;
        $this->totalBookingsCompleted = 0;
        $this->subscribersByPlan = [
            __('Basic') => 0,
            __('Premium') => 0,
            __('Elite') => 0,
        ];
    }

    public function export()
    {
        return Excel::download(new DashboardStatsExport([
            'total_transactions' => $this->totalTransactions,
            'total_revenue_dkk' => $this->totalRevenueDkk,
            'total_bookings_active' => $this->totalBookingsActive,
            'total_bookings_completed' => $this->totalBookingsCompleted,
            'subscribers_by_plan' => $this->subscribersByPlan,
        ]), 'dashboard-stats.xlsx');
    }

    public function render(): View
    {
        return view('livewire.admin.dashboard');
    }
}
