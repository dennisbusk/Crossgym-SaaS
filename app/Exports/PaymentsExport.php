<?php

declare(strict_types=1);

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PaymentsExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(protected Builder $query) {}

    public function query()
    {
        return $this->query;
    }

    public function map($payment): array
    {
        return [
            $payment->id,
            $payment->user?->name,
            $payment->amount / 100,
            $payment->currency,
            $payment->status,
            $payment->type,
            $payment->refunded_amount / 100,
            $payment->created_at,
        ];
    }

    public function headings(): array
    {
        return [
            __('ID'),
            __('User'),
            __('Amount'),
            __('Currency'),
            __('Status'),
            __('Type'),
            __('Refunded Amount'),
            __('Created At'),
        ];
    }
}
