<?php

declare( strict_types=1 );

namespace App\Livewire\Admin\Users;

use App\Models\Permission;
use App\Models\User;
use Livewire\Component;

class ManagePermissions extends Component {

    public User $user;

    /**
     * @var array<string, array<int, array{id:int, ability:string, role_granted:bool, user_override:bool|null, effective:bool}>>
     */
    public array $permissionsGrouped = [];

    public function mount( User $user ): void {
        $this->user = $user;
        $this->loadPermissions();
    }

    public function loadPermissions(): void {
        $userPerms = $this->user->permissions
            ->mapWithKeys(fn( $p ) => [ $p->id => (bool) $p->pivot->granted ]);

        $this->permissionsGrouped = Permission::query()
                                              ->orderBy('model')
                                              ->orderBy('ability')
                                              ->get()
                                              ->groupBy('model')
                                              ->map(fn( $group ) => $group->map(function ( Permission $perm ) use ( $userPerms ) {
                                                  $roleGranted = $this->user->role?->permissions?->contains('id', $perm->id) ?? false;
                                                  $override    = $userPerms[ $perm->id ] ?? null;
                                                  $effective   = $override ?? ( $this->user->role?->hasPermission($perm->model, $perm->ability) ?? false );

                                                  return [
                                                      'id'            => $perm->id,
                                                      'ability'       => $perm->ability,
                                                      'role_granted'  => $roleGranted,
                                                      'user_override' => $override,
                                                      'effective'     => (bool) $effective,
                                                  ];
                                              })->all())
                                              ->all();
    }

    public function togglePermission( int $permissionId, $forcedState = null ): void {
        $existing = $this->user->permissions()->where('permission_id', $permissionId)->first();
        if ( $forcedState === null ) {
            if ( $existing ) {
                // If override exists, remove it to revert to role behavior
                $this->user->permissions()->detach($permissionId);
            }
            else {
                // If no override, check current effective and invert it
                $perm = Permission::find($permissionId);
                if ( !$perm ) {
                    return;
                }
                $effective = $this->user->hasPermission($perm->model, $perm->ability);
                $this->user->permissions()->attach($permissionId, [ 'granted' => !$effective ]);
            }
        }
        else {
            if ( $forcedState === true ) {
                if ( !$existing ) {
                    $this->user->permissions()->attach($permissionId, [ 'granted' => $forcedState ]);
                }
            }
            else if ( $forcedState === false ) {
                if ( $existing ) {
                    $this->user->permissions()->detach($permissionId);
                }
            }
        }
        // Refresh relations to avoid stale cache
        $this->user->unsetRelation('permissions');
        $this->user->load([ 'permissions', 'role.permissions' ]);
        $this->loadPermissions();
    }

//    public function toggleAll(string $model): void
//    {
//        // Get all permissions for the given model
//        $perms = Permission::where('model', $model)->get(['id', 'model', 'ability']);
//        if ($perms->isEmpty()) {
//            return;
//        }
//
//        // Determine if all are currently effective
//        $allEffective = $perms->every(function (Permission $perm) {
//            return $this->user->hasPermission($perm->model, $perm->ability);
//        });
//
//        $target = ! $allEffective;
//
//        // Build mapping for syncWithoutDetaching to set overrides to target for all
//        $mapping = [];
//        foreach ($perms as $perm) {
//            $mapping[$perm->id] = ['granted' => $target];
//        }
//        $this->user->permissions()->syncWithoutDetaching($mapping);
//
//        // Refresh relations and reload
//        $this->user->unsetRelation('permissions');
//        $this->user->load(['permissions', 'role.permissions']);
//        $this->loadPermissions();
//    }

    public function render() {
        return view('livewire.admin.users.manage-permissions');
    }
}
