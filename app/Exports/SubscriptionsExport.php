<?php

declare(strict_types=1);

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SubscriptionsExport implements FromQuery, WithHeadings, WithMapping
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'ID',
            'User',
            'Plan',
            'Stripe Subscription ID',
            'Stripe Price ID',
            'Status',
            'Current Period End',
        ];
    }

    public function map($subscription): array
    {
        return [
            $subscription->id,
            $subscription->user?->name,
            $subscription->plan?->name,
            $subscription->stripe_subscription_id,
            $subscription->stripe_price_id,
            $subscription->status,
            $subscription->current_period_end?->format('Y-m-d H:i'),
        ];
    }
}
