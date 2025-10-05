<?php

namespace App\Livewire\Admin\Roles;

use App\Models\Role;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Rule;
use Livewire\Component;

class RoleForm extends Component
{
    use AuthorizesRequests;

    public ?Role $role = null;

    #[Rule('required|string|max:255')]
    public string $name = '';

    // Permissions entered as JSON string in the form, cast to array on save
    public ?string $permissionsInput = null;

    public function mount($role = null): void
    {
        $this->role = $role instanceof Role ? $role : new Role();
        if ($this->role && $this->role->exists) {
            $this->authorize('update', $this->role);
            $this->name = $this->role->name;
            $this->permissionsInput = $this->role->permissions ? json_encode($this->role->permissions, JSON_PRETTY_PRINT) : '';
        } else {
            $this->authorize('create', Role::class);
        }
    }

    public function save()
    {
        $this->validate();

        $permissions = null;
        if (trim((string) $this->permissionsInput) !== '') {
            $decoded = json_decode((string) $this->permissionsInput, true);
            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
                throw ValidationException::withMessages([
                    'permissionsInput' => 'Permissions must be valid JSON (an object or array).',
                ]);
            }
            $permissions = $decoded;
        }

        if ($this->role && $this->role->exists) {
            $this->role->update([
                'name' => $this->name,
                'permissions' => $permissions,
            ]);
            session()->flash('status', 'Role updated.');
        } else {
            $this->role = Role::create([
                'name' => $this->name,
                'permissions' => $permissions,
            ]);
            session()->flash('status', 'Role created.');
            return redirect()->route('roles.edit', $this->role);
        }
    }

    public function render()
    {
        return view('livewire.admin.roles.form');
    }
}
