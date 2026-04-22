<?php

namespace App\Observers;

use App\Models\AICoachSettings;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use Str;

class TenantObserver
{
    public function created(Tenant $tenant): void
    {
        $admin = Role::firstOrCreate(['slug' => Str::slug('Admin'),
            'tenant_id' => $tenant->id], [
                'name' => 'Admin',
            ]);
        $admin->permissions()->sync(Permission::query()->pluck('id'));

        Role::firstOrCreate(['slug' => Str::slug('Trainer'),
            'tenant_id' => $tenant->id], [
                'name' => 'Trainer',
            ]);
        Role::firstOrCreate(['slug' => Str::slug('Member'),
            'tenant_id' => $tenant->id], [
                'name' => 'Member',
            ]);

        $defaults = AICoachSettings::defaults();
        AICoachSettings::create([
            'tenant_id' => $tenant->id,
            'equipment' => $defaults['equipment'],
            'intensity' => $defaults['intensity'],
            'focus_area' => $defaults['focus_area'],
            'difficulty' => $defaults['difficulty'],
            'duration_min' => $defaults['duration_min'],
            'duration_max' => $defaults['duration_max'],
        ]);
    }

    public function deleted(Tenant $tenant): void {}
}
