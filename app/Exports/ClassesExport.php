<?php

declare(strict_types=1);

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ClassesExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(protected Builder $query) {}

    public function query()
    {
        return $this->query;
    }

    public function map($c): array
    {
        return [
            $c->id,
            $c->trainer_id,
            $c->class_type_id,
            $c->max_participants,
            $c->class_start,
            $c->class_end,
            $c->recurring_id,
            json_encode($c->getAttribute('name')),
            json_encode($c->getAttribute('description')),
        ];
    }

    public function headings(): array
    {
        return ['ID', 'Trainer ID', 'Class Type ID', 'Max Participants', 'Class Start', 'Class End', 'Recurring ID', 'Name', 'Description'];
    }
}
