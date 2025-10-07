<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\GymClass;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ClassesExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return GymClass::query()
            ->select(['id', 'trainer_id', 'class_type_id', 'max_participants', 'class_start', 'class_end', 'recurring_id', 'name', 'description'])
            ->get()
            ->map(function (GymClass $c) {
                return [
                    'id' => $c->id,
                    'trainer_id' => $c->trainer_id,
                    'class_type_id' => $c->class_type_id,
                    'max_participants' => $c->max_participants,
                    'class_start' => $c->class_start,
                    'class_end' => $c->class_end,
                    'recurring_id' => $c->recurring_id,
                    'name' => json_encode($c->getAttribute('name')),
                    'description' => json_encode($c->getAttribute('description')),
                ];
            });
    }

    public function headings(): array
    {
        return ['ID', 'Trainer ID', 'Class Type ID', 'Max Participants', 'Class Start', 'Class End', 'Recurring ID', 'Name', 'Description'];
    }
}
