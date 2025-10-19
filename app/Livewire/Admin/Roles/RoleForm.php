<?php

namespace App\Livewire\Admin\Roles;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Rule;
use Livewire\Component;

class RoleForm extends Component
{
    use AuthorizesRequests;

    public ?Role $role = null;
    public array $permissionsGrouped = [];


    public function loadPermissions(): void {
        $this->permissionsGrouped = Permission::query()
                                              ->orderBy('model')
                                              ->orderBy('ability')
                                              ->get()
                                              ->groupBy('model')
                                              ->map(fn( $group ) => $group->map(fn( Permission $perm ) => [
                                                  'id'      => $perm->id,
                                                  'ability' => $perm->ability,
                                                  'granted' => $this->role->permissions?->contains('id', $perm->id) ?? false,
                                              ])->all())
                                              ->all();
    }

    #[Rule('required|string|max:255')]
    public string $name = '';

    // Permissions entered as JSON string in the form, cast to array on save

    public function mount($role = null): void
    {
        $this->role = $role instanceof Role ? $role : new Role();
        if ($this->role && $this->role->exists) {
            $this->name = $this->role->name;
            $this->authorize('update', $this->role);
        } else {
            $this->authorize('create', Role::class);
        }
        $this->loadPermissions();
    }

    public function save()
    {
        $this->validate();

        if ($this->role && $this->role->exists) {
            $this->role->update([
                'name' => $this->name,
                'permissions' => $permissions,
            ]);
            session()->flash('status', 'Role updated.');
        } else {
            $this->role = Role::create([
                'name' => $this->name,
            ]);
            session()->flash('status', 'Role created.');
            return redirect()->route('roles.edit', $this->role);
        }
    }

    public function render()
    {
        return view('livewire.admin.roles.form');
    }
    public function togglePermission( int $permissionId, $forcedState = null ): void {
        if ( $forcedState === null ) {
            if ( $this->role->permissions?->contains('id', $permissionId) ) {
                $this->role->permissions()->detach($permissionId);
            }
            else {
                $this->role->permissions()->attach($permissionId);
            }
        }
        else {
            if ( $forcedState === true ) {
                if ( !$this->role->permissions?->contains('id', $permissionId) ) {
                    $this->role->permissions()->attach($permissionId);
                }
            }
            else if ( $forcedState === false ) {
                if ( $this->role->permissions?->contains('id', $permissionId) ) {
                    $this->role->permissions()->detach($permissionId);
                }
            }
        }

        // Refresh relation to avoid stale cache, then reload presentation data
        $this->role->unsetRelation('permissions');
        $this->role->load('permissions');
        $this->loadPermissions();
    }
}
