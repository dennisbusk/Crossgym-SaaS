<?php

declare(strict_types=1);

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Route;

it('superadmin can impersonate a user and then leave', function () {
    // Ensure tenant exists
    $tenant = Tenant::factory()->create();

    // Create roles
    $superAdminRole = Role::factory()->create([
        'name' => ['da' => 'SuperAdmin', 'en' => 'SuperAdmin'],
        'slug' => 'superadmin',
        'tenant_id' => null,
    ]);

    $memberRole = Role::firstOrCreate([
        'slug' => 'member',
        'tenant_id' => $tenant->id,
    ], [
        'name' => ['da' => 'Medlem', 'en' => 'Member'],
    ]);

    // Create users
    $admin = User::factory()->create([
        'role_id' => $superAdminRole->id,
        'tenant_id' => $tenant->id,
    ]);

    $target = User::factory()->create([
        'role_id' => $memberRole->id,
        'tenant_id' => $tenant->id,
    ]);

    // Sanity: routes are registered
    expect(Route::has('impersonate'))->toBeTrue();
    expect(Route::has('impersonate.leave'))->toBeTrue();

    // Start impersonation
    $this->actingAs($admin)
        ->get(route('impersonate', $target->id))
        ->assertRedirect();

    // Now authenticated as target
    $this->assertAuthenticatedAs($target);

    // Impersonator stored
    $manager = app('impersonate');
    expect($manager->getImpersonator())->not->toBeNull();
    expect($manager->getImpersonator()->id)->toBe($admin->id);

    // Leave impersonation
    $this->get(route('impersonate.leave'))
        ->assertRedirect();

    // Back to admin
    $this->assertAuthenticatedAs($admin);
});
