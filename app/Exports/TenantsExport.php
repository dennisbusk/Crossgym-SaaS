<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Tenant;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TenantsExport implements FromCollection, WithHeadings
{
    /**
     * @return Collection<int, array<string, mixed>
     */
    public function collection(): Collection
    {
        return Tenant::query()
            ->select(['id', 'name', 'domain', 'created_at'])
            ->get();
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
