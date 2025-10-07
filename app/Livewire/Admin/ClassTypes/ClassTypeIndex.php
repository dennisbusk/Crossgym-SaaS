<?php

declare(strict_types=1);

namespace App\Livewire\Admin\ClassTypes;

use App\Exports\ClassTypesExport;
use App\Models\ClassType;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class ClassTypeIndex extends Component
{
    use WithPagination;
    use AuthorizesRequests;

    #[Url]
    public string $search = '';

    public function mount(): void
    {
        $this->authorize('viewAny', ClassType::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $classType = ClassType::findOrFail($id);
        $this->authorize('delete', $classType);
        $classType->delete();
        session()->flash('status', __('Class type deleted.'));
    }

    public function export()
    {
        $this->authorize('viewAny', ClassType::class);

        return Excel::download(new ClassTypesExport(), 'class_types.xlsx');
    }

    public function render()
    {
        $classTypes = ClassType::query()
            ->when($this->search, fn ($q) => $q->where('slug', 'like', "%{$this->search}%")->orWhere('name->da', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(10);

        return view('livewire.admin.class-types.index', [
            'classTypes' => $classTypes,
        ]);
    }
}
