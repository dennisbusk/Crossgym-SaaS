<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Classes;

use App\Exports\ClassesExport;
use App\Models\GymClass;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class ClassIndex extends Component
{
    use WithPagination;
    use AuthorizesRequests;

    #[Url]
    public string $search = '';

    public function mount(): void
    {
        $this->authorize('viewAny', GymClass::class);
    }

    public function updatingSearch(): void
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

        return Excel::download(new ClassesExport(), 'classes.xlsx');
    }

    public function render()
    {
        $classes = GymClass::query()
            ->with(['trainer', 'classType'])
            ->withCount('participants')
            ->when($this->search, fn ($q) => $q->where('name->da', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(10);

        return view('livewire.admin.classes.index', [
            'classes' => $classes,
        ]);
    }
}
