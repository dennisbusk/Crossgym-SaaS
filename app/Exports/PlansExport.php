<?php

declare(strict_types=1);

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PlansExport implements FromCollection, WithHeadings
{
    public function __construct(
        protected Collection $plans
    ) {
    }

    public function collection(): Collection
    {
        return $this->plans->map(function ($plan) {
            $amount = is_null($plan->amount) ? '' : number_format(((int) $plan->amount) / 100, 2, ',', '.');

            return [
                'id' => $plan->id,
                'name' => $plan->name,
                'price' => $amount . ' ' . strtoupper((string) $plan->currency),
                'interval' => $plan->interval,
                'stripe_price_id' => $plan->stripe_price_id,
                'subscribers' => $plan->subscribers_count ?? 0,
            ];
        });
    }

    public function headings(): array
    {
        return [
            __('ID'),
            __('Name'),
            __('Price'),
            __('Interval'),
            __('Stripe Price ID'),
            __('Subscribers'),
        ];
    }
}
