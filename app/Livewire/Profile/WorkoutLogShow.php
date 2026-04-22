<?php

declare(strict_types=1);

namespace App\Livewire\Profile;

use App\Models\WorkoutLog;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class WorkoutLogShow extends Component
{
    use AuthorizesRequests;

    public WorkoutLog $workoutLog;

    public function mount(WorkoutLog $workoutLog): void
    {
        $this->authorize('view', $workoutLog);
        $this->workoutLog = $workoutLog->load('exercise');
    }

    public function render()
    {
        return view('livewire.profile.workout-log-show');
    }
}
