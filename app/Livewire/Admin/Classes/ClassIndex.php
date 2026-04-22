<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Classes;

use App\Exports\ClassesExport;
use App\Models\ClassType;
use App\Models\GymClass;
use App\Models\User;
use App\Traits\WithSorting;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class ClassIndex extends Component
{
    use AuthorizesRequests;
    use WithPagination;
    use WithSorting;

    #[Url]
    public string $search = '';

    #[Url]
    public string $fromDate = '';

    #[Url]
    public string $toDate = '';

    #[Url]
    public ?int $trainerId = null;

    #[Url]
    public ?int $classTypeId = null;

    public function mount(): void
    {
        $this->authorize('viewAny', GymClass::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFromDate(): void
    {
        $this->resetPage();
    }

    public function updatingToDate(): void
    {
        $this->resetPage();
    }

    public function updatingTrainerId(): void
    {
        $this->resetPage();
    }

    public function updatingClassTypeId(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $gymClass = GymClass::findOrFail($id);
        $this->authorize('delete', $gymClass);
        $gymClass->delete();
        session()->flash('status', __('Class deleted.'));
    }

    public function export()
    {
        $this->authorize('viewAny', GymClass::class);

        $query = GymClass::query()->with(['trainer', 'classType']);
        $query = $this->applyFilters($query);

        return Excel::download(new ClassesExport($query), 'classes.xlsx');
    }

    protected function applyFilters($query)
    {
        return $query->when($this->search, fn ($q) => $q->where('name->da', 'like', "%{$this->search}%"))
            ->when($this->fromDate, fn ($q) => $q->whereDate('class_start', '>=', $this->fromDate))
            ->when($this->toDate, fn ($q) => $q->whereDate('class_start', '<=', $this->toDate))
            ->when($this->trainerId, fn ($q) => $q->where('trainer_id', $this->trainerId))
            ->when($this->classTypeId, fn ($q) => $q->where('class_type_id', $this->classTypeId));
    }

    public function render()
    {
        $classes = GymClass::query()
            ->with(['trainer', 'classType'])
            ->withCount('participants');

        $classes = $this->applyFilters($classes);

        $classes = $this->applySorting($classes)->paginate(10);

        return view('livewire.admin.classes.index', [
            'classes' => $classes,
            'trainers' => User::query()->whereHas('role', fn ($q) => $q->whereIn('slug', ['trainer', 'admin']))->get(),
            'classTypes' => ClassType::query()->get(),
        ]);
    }
}
