<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Role;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RolesExport implements FromCollection, WithHeadings
{
    /**
     * @return Collection<int, array<string, mixed>
     */
    public function collection(): Collection
    {
        return Role::query()
            ->select(['id', 'name', 'created_at'])
            ->get();
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
