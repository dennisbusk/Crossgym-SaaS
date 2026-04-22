<?php

declare(strict_types=1);

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ClassTypesExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(protected Builder $query) {}

    public function query()
    {
        return $this->query;
    }

    public function map($t): array
    {
        return [
            $t->id,
            $t->slug,
            $t->color,
            $t->image,
            json_encode($t->getAttribute('name')),
            json_encode($t->getAttribute('description')),
            $t->created_at,
        ];
    }

    public function headings(): array
    {
        return ['ID', 'Slug', 'Color', 'Image', 'Name', 'Description', 'Created At'];
    }
}
