<?php

declare( strict_types=1 );

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Str;

class PermissionSeeder extends Seeder {

    public function run(): void {
        Artisan::call('permissions:sync');

        $admin = Role::firstOrCreate([ 'slug' => Str::slug('Admin') ]);
        $admin->permissions()->sync(Permission::query()->pluck('id'));
    }
}
