<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Roles;

use App\Exports\RolesExport;
use App\Models\Role;
use App\Traits\WithSorting;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class RoleIndex extends Component
{
    use AuthorizesRequests;
    use WithPagination;
    use WithSorting;

    #[Url]
    public string $search = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Role::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $role = Role::findOrFail($id);
        $this->authorize('delete', $role);
        $role->delete();
        session()->flash('status', __('Role deleted.'));
    }

    public function export()
    {
        $this->authorize('viewAny', Role::class);

        $query = Role::query();
        $query = $this->applyFilters($query);

        return Excel::download(new RolesExport($query), 'roles.xlsx');
    }

    protected function applyFilters($query)
    {
        return $query->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"));
    }

    public function render()
    {
        $roles = Role::query();

        $roles = $this->applyFilters($roles);

        $roles = $this->applySorting($roles)->paginate(10);

        return view('livewire.admin.roles.index', [
            'roles' => $roles,
        ]);
    }
}
