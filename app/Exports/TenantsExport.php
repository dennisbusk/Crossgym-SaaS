<?php

declare(strict_types=1);

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TenantsExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(protected Builder $query) {}

    public function query()
    {
        return $this->query;
    }

    public function map($tenant): array
    {
        return [
            $tenant->id,
            $tenant->name,
            $tenant->domain,
            $tenant->created_at,
        ];
    }

    public function headings(): array
    {
        return [
            __('ID'),
            __('Name'),
            __('Domain'),
            __('Created At'),
        ];
    }
}
