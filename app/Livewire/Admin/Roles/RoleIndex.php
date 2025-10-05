<?php

namespace App\Livewire\Admin\Roles;

use App\Models\Role;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class RoleIndex extends Component
{
    use WithPagination;
    use AuthorizesRequests;

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
        session()->flash('status', 'Role deleted.');
    }

    public function render()
    {
        $roles = Role::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(10);

        return view('livewire.admin.roles.index', [
            'roles' => $roles,
        ]);
    }
}
