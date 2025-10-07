<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersExport implements FromCollection, WithHeadings
{
    /**
     * @return Collection<int, array<string, mixed>
     */
    public function collection(): Collection
    {
        return User::query()
            ->select(['id', 'name', 'email', 'role_id', 'tenant_id', 'created_at'])
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => optional($user->role)->name,
                    'tenant' => optional($user->tenant)->name,
                    'created_at' => $user->created_at,
                ];
            });
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
