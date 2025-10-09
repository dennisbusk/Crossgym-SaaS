<?php

declare( strict_types=1 );

use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;

it('can create a permission', function () {
    $perm = Permission::factory()->create([ 'model' => 'User', 'ability' => 'view' ]);

    expect($perm->exists)->toBeTrue()
                         ->and(Permission::count())->toBe(1)
                         ->and($perm->name)->toBe('User.view');
});

it('can read a permission', function () {
    $perm = Permission::factory()->create();

    $found = Permission::find($perm->id);

    expect($found)->not->toBeNull()
                       ->and($found->id)->toBe($perm->id);
});

it('can update a permission', function () {
    $perm = Permission::factory()->create([ 'model' => 'User', 'ability' => 'view' ]);

    $perm->update([ 'ability' => 'update' ]);

    expect($perm->fresh()->name)->toBe('User.update');
});

it('can delete a permission', function () {
    $perm = Permission::factory()->create();

    $perm->delete();

    expect(Permission::find($perm->id))->toBeNull();
});

it('can attach to role and user', function () {
    $perm = Permission::factory()->create();
    $role = Role::factory()->create();
    $user = User::factory()->for(Tenant::factory())->create();

    $role->permissions()->attach($perm->id);
    $user->permissions()->attach($perm->id, [ 'granted' => true ]);

    expect($role->permissions()->count())->toBe(1)
                                         ->and($user->permissions()->count())->toBe(1);
});
