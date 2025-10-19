<?php

namespace App\Observers;

use App\Models\Role;
use App\Models\Tenant;
use Str;

class TenantObserver {

    public function created( Tenant $tenant ): void {
        Role::firstOrCreate(['slug' => Str::slug('Admin')],[
            'name' => 'Admin',
            'slug' => Str::slug('Admin'),
            'tenant_id' => $tenant->id,
        ]);

        Role::firstOrCreate(['slug' => Str::slug('Trainer')],[
            'name' => 'Trainer',
            'slug' => Str::slug('Trainer'),
            'tenant_id' => $tenant->id,
        ]);
        Role::firstOrCreate(['slug' => Str::slug('Member')],[
            'name' => 'Member',
            'slug' => Str::slug('Member'),
            'tenant_id' => $tenant->id,
        ]);
    }

    public function deleted( Tenant $tenant ): void {
    }
}
