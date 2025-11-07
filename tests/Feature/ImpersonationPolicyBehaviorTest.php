<?php

declare(strict_types=1);

use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

function makeTenantWithRoles(): array {
    $tenant = Tenant::factory()->create();

    // Global SuperAdmin role (tenant_id null)
    $superAdminRole = Role::firstOrCreate([
        'slug' => 'superadmin',
        'tenant_id' => null,
    ], [
        'name' => ['da' => 'SuperAdmin', 'en' => 'SuperAdmin'],
    ]);

    // TenantObserver creates 'member' role automatically; fetch it
    $memberRole = Role::where('slug', 'member')->where('tenant_id', $tenant->id)->firstOrFail();

    return [$tenant, $superAdminRole, $memberRole];
}

it('while impersonating, policies use impersonated user permissions (no superadmin bypass)', function () {
    [$tenant, $superAdminRole, $memberRole] = makeTenantWithRoles();

    // Users
    $admin = User::factory()->create([
        'role_id' => $superAdminRole->id,
        'tenant_id' => $tenant->id,
    ]);

    $member = User::factory()->create([
        'role_id' => $memberRole->id,
        'tenant_id' => $tenant->id,
    ]);

    // Sanity routes present
    expect(Route::has('impersonate'))->toBeTrue();
    expect(Route::has('impersonate.leave'))->toBeTrue();
    expect(Route::has('users.index'))->toBeTrue();

    // Start impersonation as admin -> member
    $this->actingAs($admin)
        ->get(route('impersonate', $member->id))
        ->assertRedirect();

    // Now, check authorization via Gate directly to avoid middleware side effects
    expect(Gate::denies('viewAny', User::class))->toBeTrue();

    // Leave impersonation, superadmin should regain access
    $this->get(route('impersonate.leave'))->assertRedirect();
    expect(Gate::allows('viewAny', User::class))->toBeTrue();
});

it('member impersonating member does not gain new abilities', function () {
    [$tenant, $superAdminRole, $memberRole] = makeTenantWithRoles();

    // Create two members
    $memberA = User::factory()->create([
        'role_id' => $memberRole->id,
        'tenant_id' => $tenant->id,
    ]);

    $memberB = User::factory()->create([
        'role_id' => $memberRole->id,
        'tenant_id' => $tenant->id,
    ]);

    // Give memberA only the ability to impersonate, nothing else
    $impersonatePerm = Permission::firstOrCreate([
        'model' => 'User',
        'ability' => 'impersonate',
    ]);
    $memberA->permissions()->attach($impersonatePerm->id, ['granted' => true]);

    expect(Route::has('impersonate'))->toBeTrue();
    expect(Route::has('users.index'))->toBeTrue();

    // Before impersonation, memberA cannot access users index
    $this->actingAs($memberA);
    expect(Gate::denies('viewAny', User::class))->toBeTrue();

    // Impersonate memberB
    $this->get(route('impersonate', $memberB->id))->assertRedirect();

    // Still cannot access users index
    expect(Gate::denies('viewAny', User::class))->toBeTrue();
});

it('superadmin not impersonating still bypasses policies', function () {
    [$tenant, $superAdminRole, $memberRole] = makeTenantWithRoles();

    $admin = User::factory()->create([
        'role_id' => $superAdminRole->id,
        'tenant_id' => $tenant->id,
    ]);

    $this->actingAs($admin);
    expect(Gate::allows('viewAny', User::class))->toBeTrue();
});
