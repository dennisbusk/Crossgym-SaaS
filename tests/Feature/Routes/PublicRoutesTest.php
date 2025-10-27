<?php

declare(strict_types=1);

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;

it('home page returns 200', function () {
    $this->get('/')->assertOk();
});

it('dashboard requires authentication', function () {
    $this->get(route('dashboard'))->assertRedirect();
});

it('dashboard returns 200 when authenticated', function () {
    // Arrange tenant and superadmin user
    $tenant = Tenant::factory()->create();
    session(['tenant_id' => $tenant->id]);

    $superRole = Role::query()->create([
        'name' => ['da' => 'Superadmin', 'en' => 'Superadmin'],
        'slug' => 'superadmin',
        'tenant_id' => null,
    ]);

    $user = User::factory()->withTenant($tenant->id)->create([
        'role_id' => $superRole->id,
    ]);

    $this->actingAs($user)->get(route('dashboard'))->assertOk();
});
