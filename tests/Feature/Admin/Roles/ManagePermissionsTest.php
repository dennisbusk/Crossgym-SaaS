<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Roles;

use App\Livewire\Admin\Roles\ManagePermissions;
use App\Models\Permission;
use App\Models\Role;
use Livewire\Livewire;

it('can copy permissions from another role', function () {
    $roleA = Role::factory()->create(['slug' => 'role-a']);
    $roleB = Role::factory()->create(['slug' => 'role-b']);

    $p1 = Permission::create(['model' => 'ModelA', 'ability' => 'view']);
    $p2 = Permission::create(['model' => 'ModelA', 'ability' => 'create']);
    $p3 = Permission::create(['model' => 'ModelB', 'ability' => 'view']);

    $roleB->permissions()->attach([$p1->id, $p2->id, $p3->id]);

    expect($roleA->permissions()->count())->toBe(0);
    expect($roleB->permissions()->count())->toBe(3);

    Livewire::test(ManagePermissions::class, ['role' => $roleA])
        ->set('copyFromRoleId', $roleB->id)
        ->call('copyPermissionsFromRole')
        ->assertDispatched('notify')
        ->assertStatus(200);

    expect($roleA->refresh()->permissions()->count())->toBe(3);
    expect($roleA->permissions()->pluck('permissions.id')->toArray())
        ->toEqualCanonicalizing([$p1->id, $p2->id, $p3->id]);
});

it('does not copy if source role is not set', function () {
    $roleA = Role::factory()->create(['slug' => 'role-a']);

    Livewire::test(ManagePermissions::class, ['role' => $roleA])
        ->call('copyPermissionsFromRole')
        ->assertStatus(200);

    expect($roleA->refresh()->permissions()->count())->toBe(0);
});

it('dispatches notify event when toggling a permission', function () {
    $role = Role::factory()->create();
    $permission = Permission::factory()->create();

    Livewire::test(ManagePermissions::class, ['role' => $role])
        ->call('togglePermission', $permission->id)
        ->assertDispatched('notify');
});

it('dispatches notify event when syncing users', function () {
    $role = Role::factory()->create();

    Livewire::test(ManagePermissions::class, ['role' => $role])
        ->call('syncUsersForRole')
        ->assertDispatched('notify');
});
