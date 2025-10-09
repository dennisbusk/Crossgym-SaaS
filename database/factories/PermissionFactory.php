<?php

declare( strict_types=1 );

namespace Database\Factories;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Permission>
 */
class PermissionFactory extends Factory {

    protected $model = Permission::class;

    public function definition(): array {
        $model   = $this->faker->randomElement([ 'User', 'Role', 'Permission', 'Tenant', 'ClassType', 'GymClass' ]);
        $ability = $this->faker->randomElement([ 'view', 'create', 'update', 'delete' ]);

        return [
            'model'   => $model,
            'ability' => $ability,
        ];
    }
}
