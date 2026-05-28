<?php

declare(strict_types=1);

namespace App\Livewire\Profile;

use App\Models\Exercise;
use App\Models\WorkoutLog;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class WorkoutLogForm extends Component
{
    use AuthorizesRequests;

    public ?WorkoutLog $workoutLog = null;

    public bool $isEditing = false;

    // Form fields
    public $date;

    public $exercise_id = '';

    public $new_exercise_name = '';

    public $category = 'strength'; // Default

    public $weight;

    public $reps;

    public $sets;

    public $distance;

    public $duration_minutes;

    public $intensity;

    public $mood;

    public $notes;

    public $prs = [];

    protected $rules = [
        'date' => 'required|date',
        'exercise_id' => 'nullable|exists:exercises,id',
        'new_exercise_name' => 'required_if:exercise_id,',
        'category' => 'required|in:strength,cardio,biometric',
        'weight' => 'nullable|numeric',
        'reps' => 'nullable|integer',
        'sets' => 'nullable|integer',
        'distance' => 'nullable|numeric',
        'duration_minutes' => 'nullable|integer',
        'intensity' => 'nullable|integer|between:1,10',
        'mood' => 'nullable|string',
        'notes' => 'nullable|string',
    ];

    public function mount(?WorkoutLog $workoutLog = null): void
    {
        if ($workoutLog && $workoutLog->exists) {
            $this->workoutLog = $workoutLog;
            $this->authorize('update', $workoutLog);
            $this->isEditing = true;
            $this->date = $workoutLog->date->format('Y-m-d');
            $this->exercise_id = $workoutLog->exercise_id;
            $this->category = $workoutLog->exercise?->category ?? 'strength';
            $this->weight = $workoutLog->weight;
            $this->reps = $workoutLog->reps;
            $this->sets = $workoutLog->sets;
            $this->distance = $workoutLog->distance;
            $this->duration_minutes = $workoutLog->duration ? (int) floor($workoutLog->duration / 60) : null;
            $this->intensity = $workoutLog->intensity;
            $this->mood = $workoutLog->mood;
            $this->notes = $workoutLog->notes;
        } else {
            $this->date = now()->format('Y-m-d');
        }
    }

    public function updatedExerciseId($value)
    {
        if ($value) {
            $exercise = Exercise::find($value);
            if ($exercise) {
                $this->category = $exercise->category;

                // Smart Autofill: Find last log for this exercise
                $lastLog = WorkoutLog::where('user_id', auth()->id())
                    ->where('exercise_id', $value)
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($lastLog) {
                    $this->weight = $lastLog->weight;
                    $this->reps = $lastLog->reps;
                    $this->sets = $lastLog->sets;
                    $this->distance = $lastLog->distance;
                    $this->duration_minutes = $lastLog->duration ? (int) floor($lastLog->duration / 60) : null;
                    $this->intensity = $lastLog->intensity;
                }
            }
        }
    }

    public function save(\App\Services\PRService $prService)
    {
        $this->validate();

        if (! $this->exercise_id && $this->new_exercise_name) {
            // Create new exercise
            $exercise = Exercise::create([
                'name' => [app()->getLocale() => $this->new_exercise_name],
                'category' => $this->category,
                'tenant_id' => auth()->user()->tenant_id,
            ]);
            $this->exercise_id = $exercise->id;
        }

        $data = [
            'tenant_id' => auth()->user()->tenant_id,
            'user_id' => auth()->id(),
            'exercise_id' => $this->exercise_id,
            'date' => $this->date,
            'weight' => $this->weight,
            'reps' => $this->reps,
            'sets' => $this->sets,
            'distance' => $this->distance,
            'duration' => $this->duration_minutes ? (int) $this->duration_minutes * 60 : null,
            'intensity' => $this->intensity,
            'mood' => $this->mood,
            'notes' => $this->notes,
        ];

        if ($this->isEditing) {
            $this->workoutLog->update($data);
            $log = $this->workoutLog;
            session()->flash('status', __('Workout log updated.'));
        } else {
            $log = WorkoutLog::create($data);
            session()->flash('status', __('Workout log created.'));
        }

        // Check for PRs to show immediate feedback
        $this->prs = $prService->evaluatePR($log);

        if (empty($this->prs)) {
            return redirect()->route('workout-logs.index');
        }

        // If PRs exist, we might want to stay on the page to show them,
        // but for now, let's just flash and redirect or show a modal.
        // For simplicity, we redirect and let the user see them in history or dashboard.
        return redirect()->route('workout-logs.index');
    }

    public function render()
    {
        return view('livewire.profile.workout-log-form', [
            'exercises' => Exercise::orderBy('name')->get(),
        ]);
    }
}
