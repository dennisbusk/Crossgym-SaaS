<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ClassType;
use App\Models\GymClass;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Str;

class GymClassSeeder extends Seeder
{
    public function run(): void
    {

        $tenant = Tenant::firstOrCreate(
            ['domain' => str_replace(['http://', 'https://'], '', config('app.url'))],
            ['name' => config('app.name', 'Crossgym Saas')]
        );

        $trainerRole = Role::firstOrCreate(['slug' => Str::slug('Trainer')], [
            'name' => 'Trainer',
            'slug' => Str::slug('Trainer'),
            'tenant_id' => $tenant->id,
        ]);
        $trainer = User::firstOrCreate(['role_id' => $trainerRole->id, 'tenant_id' => $tenant->id], ['name' => 'Trainer User', 'email' => 'trainer@example.com', 'password' => bcrypt('password')]);

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
