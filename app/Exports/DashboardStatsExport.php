<?php

declare(strict_types=1);

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DashboardStatsExport implements FromCollection, WithHeadings
{
    /** @param array<string,mixed> $data */
    public function __construct(private array $data)
    {
    }

    public function headings(): array
    {
        return [
            __('Metric'), __('Value'),
        ];
    }

    public function collection(): Collection
    {
        $rows = collect([
            [__('Total Transactions'), (string)($this->data['total_transactions'] ?? 0)],
            [__('Total Revenue (DKK)'), number_format(((int)($this->data['total_revenue_dkk'] ?? 0))/100, 2, ',', '.')],
            [__('Total Bookings (Active)'), (string)($this->data['total_bookings_active'] ?? 0)],
            [__('Total Bookings (Completed)'), (string)($this->data['total_bookings_completed'] ?? 0)],
        ]);

        $subs = $this->data['subscribers_by_plan'] ?? [];
        foreach ($subs as $plan => $total) {
            $rows->push([__('Subscribers').' - '.$plan, (string)$total]);
        }

        return $rows;
    }
}
