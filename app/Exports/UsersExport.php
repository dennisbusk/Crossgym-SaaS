<?php

declare(strict_types=1);

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(protected Builder $query) {}

    public function query()
    {
        return $this->query;
    }

    public function map($user): array
    {
        return [
            $user->id,
            $user->name,
            $user->email,
            optional($user->role)->name,
            optional($user->tenant)->name,
            $user->created_at,
        ];
    }

    public function headings(): array
    {
        return [
            __('ID'),
            __('Name'),
            __('Email'),
            __('Role'),
            __('Tenant'),
            __('Created At'),
        ];
    }
}
