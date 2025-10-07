<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ClassType;
use App\Models\GymClass;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class GymClassSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()->first() ?? Tenant::factory()->create();

        $trainer = User::whereHas('role', fn($q) => $q->where('name', 'Trainer'))
            ->where('tenant_id', $tenant->id)
            ->first();

        if (!$trainer) {
            $trainer = User::factory()->create([
                'tenant_id' => $tenant->id,
            ]);
        }

        $classType = ClassType::query()->where('tenant_id', $tenant->id)->first() ?? ClassType::factory()->create([
            'tenant_id' => $tenant->id,
        ]);

        GymClass::factory()->count(10)->create([
            'tenant_id' => $tenant->id,
            'trainer_id' => $trainer->id,
            'class_type_id' => $classType->id,
        ]);
    }
}
