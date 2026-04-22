<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\GymClass;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ClassRecurringService
{
    /**
     * Generate recurring classes based on an initial class and interval.
     *
     * @param  GymClass  $base  The base class to copy from.
     * @param  string  $interval  daily|weekly|monthly
     * @param  int  $occurrences  Number of additional occurrences to create
     * @return array<int, GymClass>
     */
    public function generate(GymClass $base, string $interval, int $occurrences = 8): array
    {
        $recurringId = (string) Str::uuid();

        // Update base with recurring_id
        $base->recurring_id = $recurringId;
        $base->save();

        $created = [$base];

        $start = Carbon::parse($base->class_start);
        $end = Carbon::parse($base->class_end);

        for ($i = 1; $i <= $occurrences; $i++) {
            [$nextStart, $nextEnd] = $this->nextWindow($start, $end, $interval);

            $created[] = GymClass::create([
                'tenant_id' => $base->tenant_id,
                'name' => $base->getAttribute('name'),
                'description' => $base->getAttribute('description'),
                'trainer_id' => $base->trainer_id,
                'class_type_id' => $base->class_type_id,
                'max_participants' => $base->max_participants,
                'class_start' => $nextStart,
                'class_end' => $nextEnd,
                'recurring_id' => $recurringId,
            ]);

            $start = $nextStart;
            $end = $nextEnd;
        }

        return $created;
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function nextWindow(Carbon $start, Carbon $end, string $interval): array
    {
        $nextStart = $start->copy();
        $nextEnd = $end->copy();

        switch ($interval) {
            case 'daily':
                $nextStart->addDay();
                $nextEnd->addDay();
                break;
            case 'weekly':
                $nextStart->addWeek();
                $nextEnd->addWeek();
                break;
            case 'monthly':
                $nextStart->addMonth();
                $nextEnd->addMonth();
                break;
            default:
                throw new \InvalidArgumentException('Unsupported interval: '.$interval);
        }

        return [$nextStart, $nextEnd];
    }
}
