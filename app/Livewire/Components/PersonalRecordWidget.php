<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\WorkoutLog;
use App\Models\UserDashboardWidget;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class PersonalRecordWidget extends Component
{
    public ?int $widgetId = null;

    public function remove(): void
    {
        if ($this->widgetId) {
            UserDashboardWidget::where('id', $this->widgetId)
                ->where('user_id', Auth::id())
                ->delete();

            $this->dispatch('widget-removed');
        }
    }

    public function render(): View
    {
        $userId = Auth::id();

        // Find max weight for strength exercises
        // We use whereIn with a subquery to get the full record for the max weight
        $strengthPrs = WorkoutLog::where('user_id', $userId)
            ->whereIn(DB::raw('(exercise_id, weight)'), function ($query) use ($userId) {
                $query->select('exercise_id', DB::raw('MAX(weight)'))
                    ->from('workout_logs')
                    ->where('user_id', $userId)
                    ->whereNotNull('weight')
                    ->groupBy('exercise_id');
            })
            ->with('exercise')
            ->get()
            ->unique('exercise_id');

        // Find records for cardio exercises
        // Currently we define cardio PR as the minimum duration for exercises with distance > 0
        $cardioPrs = WorkoutLog::where('user_id', $userId)
            ->where('distance', '>', 0)
            ->where('duration', '>', 0)
            ->whereIn(DB::raw('(exercise_id, duration)'), function ($query) use ($userId) {
                $query->select('exercise_id', DB::raw('MIN(duration)'))
                    ->from('workout_logs')
                    ->where('user_id', $userId)
                    ->where('distance', '>', 0)
                    ->where('duration', '>', 0)
                    ->groupBy('exercise_id');
            })
            ->with('exercise')
            ->get()
            ->unique('exercise_id');

        return view('livewire.components.personal-record-widget', [
            'strengthPrs' => $strengthPrs,
            'cardioPrs' => $cardioPrs,
        ]);
    }
}
