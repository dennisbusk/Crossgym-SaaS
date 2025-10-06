<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Roles;

use App\Models\Role;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class RoleShow extends Component
{
    use AuthorizesRequests;

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
        ]);
    }
}
