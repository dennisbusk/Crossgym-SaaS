<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ClassType;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Str;

class ClassTypeSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure a tenant exists
        $tenant = Tenant::firstOrCreate(
            ['domain' => str_replace(['http://','https://'],'',config('app.url'))]
            ,
            ['name' => config('app.name','Crossgym Saas')]
        );
        Tenant::firstOrCreate(['domain' => str_replace('.test','-2.test',str_replace(['http://','https://'],'',config('app.url')))],['name' => config('app.name','Crossgym Saas').'-2']);
        // Ensure roles exist
        $superAdminRole = Role::firstOrCreate(['slug' => Str::slug('Superadmin')],[
            'name' => 'Superadmin',
            'slug' => Str::slug('Superadmin'),
            'tenant_id' => 1,
        ]);
        $adminRole = Role::firstOrCreate(['slug' => Str::slug('Admin')],[
            'name' => 'Admin',
            'slug' => Str::slug('Admin'),
            'tenant_id' => $tenant->id,
        ]);

        $trainerRole = Role::firstOrCreate(['slug' => Str::slug('Trainer')],[
            'name' => 'Trainer',
            'slug' => Str::slug('Trainer'),
            'tenant_id' => $tenant->id,
        ]);
        $memberRole = Role::firstOrCreate(['slug' => Str::slug('Member')],[
            'name' => 'Member',
            'slug' => Str::slug('Member'),
            'tenant_id' => $tenant->id,
        ]);

        // Ensure a couple of users exist
        User::firstOrCreate([
            'email' => 'superadmin@db.dk',
        ], [
            'name' => 'Super Admin User',
            'password' => bcrypt('password'),
            'role_id' => $superAdminRole->id,
            'tenant_id' => 1,
        ]);
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
