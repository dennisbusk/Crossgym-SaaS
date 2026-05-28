<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\Exercise;
use App\Models\UserDashboardWidget;
use App\Models\WorkoutLog;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ExerciseProgressWidget extends Component
{
    public ?int $exerciseId = null;

    public ?int $widgetId = null;

    /** @var array{labels: array<string>, data: array<float>} */
    public array $chartData = ['labels' => [], 'data' => []];

    public function mount(): void
    {
        if ($this->widgetId) {
            $widget = UserDashboardWidget::find($this->widgetId);
            if ($widget && isset($widget->settings['exercise_id'])) {
                $this->exerciseId = (int) $widget->settings['exercise_id'];
            }
        }
        $this->loadData();
    }

    public function updatedExerciseId(): void
    {
        if ($this->widgetId) {
            $widget = UserDashboardWidget::find($this->widgetId);
            if ($widget) {
                $settings = $widget->settings ?? [];
                $settings['exercise_id'] = $this->exerciseId;
                $widget->update(['settings' => $settings]);
            }
        }
        $this->loadData();
    }

    public function remove(): void
    {
        if ($this->widgetId) {
            UserDashboardWidget::where('id', $this->widgetId)
                ->where('user_id', Auth::id())
                ->delete();

            $this->dispatch('widget-removed');
        }
    }

    public function loadData(): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        if (! $this->exerciseId) {
            $lastLog = WorkoutLog::where('user_id', $user->id)
                ->latest('date')
                ->first();

            $this->exerciseId = $lastLog?->exercise_id;
        }

        if ($this->exerciseId) {
            $logs = WorkoutLog::where('user_id', $user->id)
                ->where('exercise_id', $this->exerciseId)
                ->orderBy('date')
                ->get();

            $this->chartData = [
                'labels' => $logs->map(fn ($log) => $log->date->format('d/m'))->toArray(),
                'data' => $logs->map(fn ($log) => (float) $log->weight)->toArray(),
            ];
        } else {
            $this->chartData = ['labels' => [], 'data' => []];
        }
    }

    public function render(): View
    {
        $exercises = Exercise::whereHas('workoutLogs', function ($query) {
            $query->where('user_id', Auth::id());
        })->get();

        return view('livewire.components.exercise-progress-widget', [
            'exercises' => $exercises,
        ]);
    }
}
