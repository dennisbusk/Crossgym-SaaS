<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\ClassType;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ClassTypesExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return ClassType::query()
            ->select(['id', 'slug', 'color', 'image', 'name', 'description', 'created_at'])
            ->get()
            ->map(function (ClassType $t) {
                return [
                    'id' => $t->id,
                    'slug' => $t->slug,
                    'color' => $t->color,
                    'image' => $t->image,
                    'name' => json_encode($t->getAttribute('name')),
                    'description' => json_encode($t->getAttribute('description')),
                    'created_at' => $t->created_at,
                ];
            });
    }

    public function headings(): array
    {
        return ['ID', 'Slug', 'Color', 'Image', 'Name', 'Description', 'Created At'];
    }
}
