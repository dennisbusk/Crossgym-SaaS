<?php

namespace App\Events;

use App\Models\WorkoutLog;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkoutLogCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public WorkoutLog $workoutLog
    ) {}
}
