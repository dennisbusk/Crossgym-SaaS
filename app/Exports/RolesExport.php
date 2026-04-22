<?php

declare(strict_types=1);

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RolesExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(protected Builder $query) {}

    public function query()
    {
        return $this->query;
    }

    public function map($role): array
    {
        return [
            $role->id,
            $role->name,
            $role->created_at,
        ];
    }

    public function headings(): array
    {
        return [
            __('ID'),
            __('Name'),
            __('Created At'),
        ];
    }
}
