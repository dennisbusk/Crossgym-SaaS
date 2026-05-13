<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Roles;

use App\Livewire\Admin\Roles\RoleShow;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Livewire\Livewire;

it('can impersonate a user from the role show page', function () {
    $tenant = Tenant::factory()->create();

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

    $admin = User::factory()->create([
        'role_id' => $superAdminRole->id,
        'tenant_id' => $tenant->id,
    ]);

    $target = User::factory()->create([
        'role_id' => $memberRole->id,
        'tenant_id' => $tenant->id,
    ]);

    Livewire::actingAs($admin)
        ->test(RoleShow::class, ['role' => $memberRole])
        ->assertSee($target->name)
        ->call('impersonate', $target->id)
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($target);
    expect(app('impersonate')->isImpersonating())->toBeTrue();
});
