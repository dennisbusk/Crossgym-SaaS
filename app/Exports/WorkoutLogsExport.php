<?php

declare(strict_types=1);

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class WorkoutLogsExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(protected Builder $query) {}

    public function query()
    {
        return $this->query;
    }

    public function map($log): array
    {
        return [
            $log->id,
            $log->date->format('Y-m-d'),
            optional($log->exercise)->name ?? __('Unknown'),
            $log->weight,
            $log->reps,
            $log->sets,
            $log->distance,
            $log->duration,
            $log->intensity,
            $log->mood,
            $log->notes,
        ];
    }

    public function headings(): array
    {
        return [
            __('ID'),
            __('Date'),
            __('Exercise'),
            __('Weight'),
            __('Reps'),
            __('Sets'),
            __('Distance'),
            __('Duration'),
            __('Intensity'),
            __('Mood'),
            __('Notes'),
        ];
    }
}
