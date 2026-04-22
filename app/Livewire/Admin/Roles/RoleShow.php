<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Roles;

use App\Models\Role;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class RoleShow extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public Role $role;

    public function mount(Role $role): void
    {
        $this->authorize('view', $role);
        $this->role = $role;
    }

    public function render()
    {
        return view('livewire.admin.roles.show', [
            'role' => $this->role,
            'users' => $this->role->users()->paginate(10),
        ]);
    }
}
