<?php

use App\Livewire\Auth\Login;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('ensures superadmin user exists on login attempt', function () {
    // Ensure tenant 1 exists to satisfy foreign key
    \App\Models\Tenant::firstOrCreate(['id' => 1], [
        'name' => 'Default Tenant',
        'domain' => 'default.com',
    ]);

    // Manually delete if exists to start clean
    User::where('email', 'dennis@db-development.dk')->delete();
    Role::where('slug', 'superadmin')->delete();

    Livewire::test(Login::class)
        ->set('email', 'dennis@db-development.dk')
        ->set('password', 'password')
        ->call('login');

    $role = Role::withoutGlobalScopes()->where('slug', 'superadmin')->first();
    $user = User::where('email', 'dennis@db-development.dk')->first();

    expect($role)->not->toBeNull()
        ->and($user)->not->toBeNull()
        ->and($user->role_id)->toBe($role->id)
        ->and($user->email)->toBe('dennis@db-development.dk');
});

it('ensures superadmin user exists via service provider boot', function () {
    // Ensure tenant 1 exists to satisfy foreign key
    \App\Models\Tenant::firstOrCreate(['id' => 1], [
        'name' => 'Default Tenant',
        'domain' => 'default.com',
    ]);

    // Manually delete if exists to start clean
    User::where('email', 'dennis@db-development.dk')->delete();
    Role::where('slug', 'superadmin')->delete();
    Cache::forget('superadmin_ensured');

    // Manually call boot logic
    $provider = new \App\Providers\AppServiceProvider(app());
    $provider->boot();

    $role = Role::withoutGlobalScopes()->where('slug', 'superadmin')->first();
    $user = User::where('email', 'dennis@db-development.dk')->first();

    expect($role)->not->toBeNull()
        ->and($user)->not->toBeNull()
        ->and($user->role_id)->toBe($role->id)
        ->and($user->email)->toBe('dennis@db-development.dk');
});
