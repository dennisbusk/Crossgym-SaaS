<?php

declare( strict_types=1 );

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;

it('can create a user', function () {
    $user = User::factory()
                ->for(Tenant::factory())
                ->for(Role::factory())
                ->create();

    expect($user->exists)->toBeTrue()
                         ->and(User::count())->toBe(1)
                         ->and($user->tenant)->not->toBeNull()
                                                  ->and($user->role)->not->toBeNull();
});

it('can read a user', function () {
    $user = User::factory()->for(Tenant::factory())->for(Role::factory())->create();

    $found = User::find($user->id);

    expect($found)->not->toBeNull()
                       ->and($found->id)->toBe($user->id);
});

it('can update a user', function () {
    $user = User::factory()->for(Tenant::factory())->for(Role::factory())->create([ 'name' => 'John Doe' ]);

    $user->update([ 'name' => 'Jane Doe' ]);

    expect($user->fresh()->name)->toBe('Jane Doe');
});

it('can delete a user', function () {
    $user = User::factory()->for(Tenant::factory())->for(Role::factory())->create();

    $user->delete();

    expect(User::find($user->id))->toBeNull();
});
