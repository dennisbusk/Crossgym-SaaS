<?php

declare(strict_types=1);

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PlansExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        protected Builder $query
    ) {}

    public function query()
    {
        return $this->query;
    }

    public function map($plan): array
    {
        $amount = is_null($plan->amount) ? '' : number_format(((int) $plan->amount) / 100, 2, ',', '.');

        return [
            $plan->id,
            $plan->name,
            $amount.' '.strtoupper((string) $plan->currency),
            $plan->interval,
            $plan->stripe_price_id,
            $plan->subscribers_count ?? 0,
        ];
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
