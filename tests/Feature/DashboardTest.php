<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('authenticated users can visit the dashboard', function () {
    $tenant = \App\Models\Tenant::factory()->create();
    $superRole = \App\Models\Role::withoutGlobalScopes()->updateOrCreate(
        ['slug' => 'superadmin'],
        ['name' => ['da' => 'Superadmin', 'en' => 'Superadmin'], 'tenant_id' => null]
    );

    $user = User::factory()->withTenant($tenant->id)->create([
        'role_id' => $superRole->id,
    ]);

    $this->actingAs($user);

    $this->get('/dashboard')->assertStatus(200);
});
