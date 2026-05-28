<?php

declare(strict_types=1);

namespace App\Livewire\Profile;

use App\Exports\WorkoutLogsExport;
use App\Models\WorkoutLog;
use App\Traits\WithSorting;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class WorkoutLogIndex extends Component
{
    use AuthorizesRequests;
    use WithPagination;
    use WithSorting;

    public $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(WorkoutLog $workoutLog): void
    {
        $this->authorize('delete', $workoutLog);
        $workoutLog->delete();
        session()->flash('status', __('Workout log deleted.'));
    }

    public function export()
    {
        $query = WorkoutLog::query()
            ->where('user_id', auth()->id())
            ->with('exercise');

        if ($this->search) {
            $query->whereHas('exercise', function ($q) {
                $q->where('name->'.app()->getLocale(), 'like', '%'.$this->search.'%');
            });
        }

        return Excel::download(new WorkoutLogsExport($query), 'workout-logs.xlsx');
    }

    public function render()
    {
        $query = WorkoutLog::query()
            ->where('user_id', auth()->id())
            ->with('exercise');

        if ($this->search) {
            $query->whereHas('exercise', function ($q) {
                $q->where('name->'.app()->getLocale(), 'like', '%'.$this->search.'%');
            });
        }

        $logs = $this->applySorting($query)->paginate(10);

        return view('livewire.profile.workout-log-index', [
            'logs' => $logs,
        ]);
    }
}
