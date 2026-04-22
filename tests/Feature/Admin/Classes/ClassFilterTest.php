<?php

declare(strict_types=1);

use App\Livewire\Admin\Classes\ClassIndex;
use App\Models\ClassType;
use App\Models\GymClass;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    // Disable global tenant scope if it exists for these models in tests
    // Or just make sure everything belongs to the same tenant
    $this->tenant = Tenant::firstOrCreate(
        ['domain' => 'test.com'],
        ['name' => 'Test Tenant']
    );
    session(['tenant_id' => $this->tenant->id]);

    $this->adminRole = Role::firstOrCreate(
        ['slug' => 'administrator', 'tenant_id' => $this->tenant->id],
        ['name' => ['da' => 'Administrator', 'en' => 'Administrator']]
    );
    $this->trainerRole = Role::firstOrCreate(
        ['slug' => 'trainer', 'tenant_id' => $this->tenant->id],
        ['name' => ['da' => 'Trainer', 'en' => 'Trainer']]
    );

    $this->defaultType = ClassType::factory()->create(['tenant_id' => $this->tenant->id]);

    $this->defaultTrainer = User::factory()->create([
        'role_id' => $this->trainerRole->id,
        'tenant_id' => $this->tenant->id,
    ]);

    $this->admin = User::factory()->create([
        'role_id' => $this->adminRole->id,
        'tenant_id' => $this->tenant->id,
    ]);

    $permission = Permission::firstOrCreate(['model' => 'GymClass', 'ability' => 'viewAny']);
    $this->admin->permissions()->attach($permission->id, ['granted' => true]);
});

test('can filter classes by search', function () {
    $class1 = GymClass::factory()->create([
        'tenant_id' => $this->tenant->id,
        'trainer_id' => $this->defaultTrainer->id,
        'class_type_id' => $this->defaultType->id,
        'name' => ['da' => 'Yoga Class', 'en' => 'Yoga Class'],
    ]);
    $class2 = GymClass::factory()->create([
        'tenant_id' => $this->tenant->id,
        'trainer_id' => $this->defaultTrainer->id,
        'class_type_id' => $this->defaultType->id,
        'name' => ['da' => 'Crossfit Class', 'en' => 'Crossfit Class'],
    ]);

    Livewire::actingAs($this->admin)
        ->test(ClassIndex::class)
        ->set('search', 'Yoga')
        ->assertSee('Yoga Class')
        ->assertDontSee('Crossfit Class');
});

test('can filter classes by date range', function () {
    $class1 = GymClass::factory()->create([
        'tenant_id' => $this->tenant->id,
        'trainer_id' => $this->defaultTrainer->id,
        'class_type_id' => $this->defaultType->id,
        'class_start' => '2026-05-01 10:00:00',
        'name' => ['da' => 'May Class'],
    ]);
    $class2 = GymClass::factory()->create([
        'tenant_id' => $this->tenant->id,
        'trainer_id' => $this->defaultTrainer->id,
        'class_type_id' => $this->defaultType->id,
        'class_start' => '2026-06-01 10:00:00',
        'name' => ['da' => 'June Class'],
    ]);

    Livewire::actingAs($this->admin)
        ->test(ClassIndex::class)
        ->set('fromDate', '2026-05-01')
        ->set('toDate', '2026-05-31')
        ->assertSee('May Class')
        ->assertDontSee('June Class');
});

test('can filter classes by trainer', function () {
    $trainer1 = User::factory()->create(['role_id' => $this->trainerRole->id, 'tenant_id' => $this->tenant->id, 'name' => 'Trainer One']);
    $trainer2 = User::factory()->create(['role_id' => $this->trainerRole->id, 'tenant_id' => $this->tenant->id, 'name' => 'Trainer Two']);

    $class1 = GymClass::factory()->create([
        'tenant_id' => $this->tenant->id,
        'trainer_id' => $trainer1->id,
        'class_type_id' => $this->defaultType->id,
        'name' => ['da' => 'Trainer One Class'],
    ]);
    $class2 = GymClass::factory()->create([
        'tenant_id' => $this->tenant->id,
        'trainer_id' => $trainer2->id,
        'class_type_id' => $this->defaultType->id,
        'name' => ['da' => 'Trainer Two Class'],
    ]);

    Livewire::actingAs($this->admin)
        ->test(ClassIndex::class)
        ->set('trainerId', $trainer1->id)
        ->assertSee('Trainer One Class')
        ->assertDontSee('Trainer Two Class');
});

test('can filter classes by class type', function () {
    $type1 = ClassType::factory()->create(['tenant_id' => $this->tenant->id, 'name' => ['da' => 'Type One']]);
    $type2 = ClassType::factory()->create(['tenant_id' => $this->tenant->id, 'name' => ['da' => 'Type Two']]);

    $class1 = GymClass::factory()->create([
        'tenant_id' => $this->tenant->id,
        'trainer_id' => $this->defaultTrainer->id,
        'class_type_id' => $type1->id,
        'name' => ['da' => 'Type One Class'],
    ]);
    $class2 = GymClass::factory()->create([
        'tenant_id' => $this->tenant->id,
        'trainer_id' => $this->defaultTrainer->id,
        'class_type_id' => $type2->id,
        'name' => ['da' => 'Type Two Class'],
    ]);

    Livewire::actingAs($this->admin)
        ->test(ClassIndex::class)
        ->set('classTypeId', $type1->id)
        ->assertSee('Type One Class')
        ->assertDontSee('Type Two Class');
});
