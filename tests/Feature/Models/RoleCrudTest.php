<?php

declare(strict_types=1);

use App\Models\Permission;
use App\Models\Role;

it('can create a role', function () {
    $role = Role::factory()->create();

    expect($role->exists)->toBeTrue()
        ->and(Role::count())->toBe(1)
        ->and($role->slug)->not->toBe('');
});

it('can read a role', function () {
    $role = Role::factory()->create();

    $found = Role::find($role->id);

    expect($found)->not->toBeNull()
        ->and($found->id)->toBe($role->id);
});

it('can update a role', function () {
    $role = Role::factory()->create(['name' => ['da' => 'Træner', 'en' => 'Trainer']]);

    $role->update(['name' => ['da' => 'Admin', 'en' => 'Admin']]);

    expect($role->fresh()->getTranslation('name', 'en'))->toBe('Admin');
});

it('can delete a role', function () {
    $role = Role::factory()->create();

    $role->delete();

    expect(Role::find($role->id))->toBeNull();
});

// it('can attach permissions to role', function () {
//    $role = Role::factory()->create();
//    $perm = Permission::factory()->create();
//
//    $role->permissions()->attach($perm->id);
//
//    expect($role->permissions()->count())->toBe(1);
// });
