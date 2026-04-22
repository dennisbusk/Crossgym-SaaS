<?php

declare(strict_types=1);

use App\Livewire\Components\GymClassCalendar;
use App\Models\ClassType;
use App\Models\GymClass;
use App\Models\Permission;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Livewire\Livewire;

function makeUser(string $email = 'u@example.com'): User
{
    $tenant = Tenant::factory()->create();
    $role = \App\Models\Role::query()->firstOrCreate(
        ['slug' => 'member', 'tenant_id' => $tenant->id],
        ['name' => 'Member']
    );

    $user = User::query()->create([
        'name' => 'Member User',
        'email' => $email,
        'password' => 'secret-pass-123',
        'role_id' => $role->id,
        'tenant_id' => $tenant->id,
    ]);

    $viewPermission = Permission::query()->firstOrCreate([
        'model' => 'GymClass',
        'ability' => 'view',
    ]);
    $user->permissions()->syncWithoutDetaching([$viewPermission->id => ['granted' => true]]);

    return $user;
}

function makeClass(User $user): GymClass
{
    $ct = ClassType::query()->create([
        'tenant_id' => $user->tenant_id,
        'name' => ['da' => 'Yoga'],
        'description' => ['da' => ''],
        'color' => '#22c55e',
        'slug' => 'yoga-'.uniqid(),
    ]);

    return GymClass::query()->create([
        'tenant_id' => $user->tenant_id,
        'name' => ['da' => 'Morgen Yoga'],
        'description' => ['da' => ''],
        'trainer_id' => $user->id,
        'class_type_id' => $ct->id,
        'max_participants' => 10,
        'class_start' => now()->addDay(),
        'class_end' => now()->addDay()->addHour(),
    ]);
}

it('books with one_off credits and decrements credits', function () {
    $user = makeUser('oneoff@example.com');
    $this->actingAs($user);

    // one_off credits
    Subscription::query()->updateOrCreate(
        ['tenant_id' => $user->tenant_id, 'user_id' => $user->id],
        [
            'stripe_subscription_id' => 'sub_test_'.$user->id,
            'stripe_price_id' => 'price_test_one_off',
            'plan_type' => 'one_off',
            'status' => 'active',
            'credits_remaining' => 1,
        ]
    );

    $class = makeClass($user);

    Livewire::test(GymClassCalendar::class)
        ->call('book', $class->id);

    $user->refresh();
    $class->refresh();
    $sub = Subscription::query()->where('user_id', $user->id)->first();

    expect($class->participants()->whereKey($user->id)->exists())->toBeTrue();
    expect((int) $sub->credits_remaining)->toBe(0);
});

it('blocks booking for past_due subscription', function () {
    $user = makeUser('sub@example.com');
    $this->actingAs($user);

    Subscription::query()->updateOrCreate(
        ['tenant_id' => $user->tenant_id, 'user_id' => $user->id],
        [
            'stripe_subscription_id' => 'sub_test_'.$user->id,
            'stripe_price_id' => 'price_test_subscription',
            'plan_type' => 'subscription',
            'status' => 'past_due',
        ]
    );

    $class = makeClass($user);

    Livewire::test(GymClassCalendar::class)
        ->call('book', $class->id);

    $class->refresh();
    expect($class->participants()->whereKey($user->id)->exists())->toBeFalse();
});
