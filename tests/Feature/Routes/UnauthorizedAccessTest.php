<?php

declare(strict_types=1);

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function actingAsBasicMember(): array {
    $tenant = Tenant::factory()->create();
    $role = Role::factory()->create(['slug' => 'member']); // no explicit permissions
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'role_id'   => $role->id,
    ]);

    test()->actingAs($user);
    test()->withSession(['tenant_id' => $tenant->id]);
    app()->instance('tenant', $tenant);

    return [$user, $tenant, $role];
}

it('user without explicit permissions can access auth-only routes', function () {
    [$user, $tenant, $role] = actingAsBasicMember();

    // Routes that only require authentication (no policy checks)
    $authOnlyRoutes = [
        'dashboard',
        'calendar',
        'settings.profile',
        'settings.password',
        'settings.appearance',
        'tenant.subscription',
    ];

    foreach ($authOnlyRoutes as $name) {
        expect(\Illuminate\Support\Facades\Route::has($name))->toBeTrue();
        $this->get(route($name))->assertOk();
    }
});
