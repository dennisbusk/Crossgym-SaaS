<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ClassType;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClassTypeSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure a tenant exists
        $tenant = Tenant::query()->first() ?? Tenant::factory()->create();

        // Ensure roles exist
        $adminRole = Role::firstOrCreate([
            'name' => 'Admin',
            'tenant_id' => $tenant->id,
        ], [
            'permissions' => [],
        ]);

        $trainerRole = Role::firstOrCreate([
            'name' => 'Trainer',
            'tenant_id' => $tenant->id,
        ], [
            'permissions' => [],
        ]);

        // Ensure a couple of users exist
        User::firstOrCreate([
            'email' => 'admin@example.com',
        ], [
            'name' => 'Admin User',
            'password' => bcrypt('password'),
            'role_id' => $adminRole->id,
            'tenant_id' => $tenant->id,
        ]);

        User::firstOrCreate([
            'email' => 'trainer@example.com',
        ], [
            'name' => 'Trainer User',
            'password' => bcrypt('password'),
            'role_id' => $trainerRole->id,
            'tenant_id' => $tenant->id,
        ]);

        // Create some class types
        ClassType::factory()->count(5)->create([
            'tenant_id' => $tenant->id,
        ]);
    }
}
