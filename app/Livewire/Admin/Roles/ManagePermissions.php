<?php

declare( strict_types=1 );

namespace App\Livewire\Admin\Roles;

use App\Models\Permission;
use App\Models\Role;
use Livewire\Component;

class ManagePermissions extends Component {

    public Role $role;

    /**
     * Grouped permissions data structure used by the view.
     * @var array<string, array<int, array{id:int, ability:string, granted:bool}>>
     */
    public array $permissionsGrouped = [];


    public function mount( Role $role ): void {
        $this->role = $role;
        $this->loadPermissions();
    }

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

    public function syncUsersForRole(): void
    {
        $this->role->load('users', 'permissions');
        foreach ($this->role->users as $user) {
            $user->syncPermissionsFromRole($this->role);
        }
        session()->flash('status', __('Users synced to role permissions.'));
    }

//    public function toggleAll(string $model): void
//    {
//        $ids = Permission::where('model', $model)->pluck('id')->all();
//        $current = $this->role->permissions()->whereIn('permissions.id', $ids)->pluck('permissions.id')->all();
//
//        if (count($ids) === 0) {
//            return;
//        }
//
//        if (count($current) === count($ids)) {
//            // All granted -> revoke all in this model
//            $this->role->permissions()->detach($ids);
//        } else {
//            // Grant missing ones
//            $toAttach = array_values(array_diff($ids, $current));
//            if (! empty($toAttach)) {
//                $this->role->permissions()->attach($toAttach);
//            }
//        }
//
//        $this->role->unsetRelation('permissions');
//        $this->role->load('permissions');
//        $this->loadPermissions();
//    }

    public function render() {
        return view('livewire.admin.roles.manage-permissions');
    }
}
