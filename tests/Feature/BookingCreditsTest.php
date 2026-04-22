<?php

declare(strict_types=1);

use App\Livewire\Profile\Bookings;
use App\Models\ClassType;
use App\Models\GymClass;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Livewire\Livewire;

it('restores one_off credit when user cancels booking before class start', function () {
    $tenant = Tenant::factory()->create();
    $role = \App\Models\Role::query()->firstOrCreate(['slug' => 'member'], ['name' => 'Member']);
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'role_id' => $role->id,
    ]);
    $classType = ClassType::factory()->create(['tenant_id' => $tenant->id]);

    Subscription::query()->updateOrCreate(
        ['tenant_id' => $tenant->id, 'user_id' => $user->id],
        [
            'stripe_subscription_id' => 'sub_test_'.$user->id,
            'stripe_price_id' => 'price_test_one_off',
            'plan_type' => 'one_off',
            'status' => 'active',
            'credits_remaining' => 2,
        ]
    );

    $class = GymClass::create([
        'tenant_id' => $tenant->id,
        'name' => ['da' => 'Test Class'],
        'description' => ['da' => ''],
        'trainer_id' => $user->id,
        'class_type_id' => $classType->id,
        'max_participants' => 10,
        'class_start' => now()->addDay(),
        'class_end' => now()->addDay()->addHour(),
        'recurring_id' => null,
    ]);
    $class->participants()->attach($user->id);

    $sub = Subscription::query()->where('user_id', $user->id)->first();
    $sub->decrement('credits_remaining');
    expect((int) $sub->fresh()->credits_remaining)->toBe(1);

    $permission = \App\Models\Permission::query()->firstOrCreate(
        ['model' => 'GymClass', 'ability' => 'view'],
        ['name' => 'View GymClass']
    );
    $user->permissions()->syncWithoutDetaching([$permission->id => ['granted' => true]]);

    $this->actingAs($user);

    Livewire::test(Bookings::class)
        ->call('cancelBooking', $class->id);

    $sub->refresh();
    expect((int) $sub->credits_remaining)->toBe(2);
    expect($class->participants()->whereKey($user->id)->exists())->toBeFalse();
});
