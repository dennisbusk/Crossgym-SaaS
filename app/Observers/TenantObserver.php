<?php

namespace App\Observers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use Str;

class TenantObserver {

    public function created( Tenant $tenant ): void {
        $admin = Role::firstOrCreate(['slug' => Str::slug('Admin'),
                             'tenant_id' => $tenant->id],[
            'name' => 'Admin'
        ]);
        $admin->permissions()->sync(Permission::query()->pluck('id'));

        Role::firstOrCreate(['slug' => Str::slug('Trainer'),
                             'tenant_id' => $tenant->id],[
            'name' => 'Trainer',
        ]);
        Role::firstOrCreate(['slug' => Str::slug('Member'),
                             'tenant_id' => $tenant->id],[
            'name' => 'Member'
        ]);
    }

    public function deleted( Tenant $tenant ): void {

    }
}
