<?php

use App\Livewire\Admin\Classes\ClassShow;
use App\Models\GymClass;
use App\Models\GymClassTrial;
use App\Models\Tenant;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->admin = User::factory()->create([
        'tenant_id' => $this->tenant->id,
    ]);

    // Give permissions
    $permissions = [
        ['model' => 'GymClass', 'ability' => 'view'],
        ['model' => 'GymClass', 'ability' => 'update'],
    ];

    foreach ($permissions as $p) {
        $perm = \App\Models\Permission::create($p);
        $this->admin->permissions()->attach($perm, ['granted' => true]);
    }

    $this->classType = \App\Models\ClassType::factory()->create([
        'tenant_id' => $this->tenant->id,
    ]);
    // Assign role with permissions if needed, but for now assuming direct auth or super admin
    $this->gymClass = GymClass::factory()->create([
        'tenant_id' => $this->tenant->id,
        'trainer_id' => $this->admin->id,
        'class_type_id' => $this->classType->id,
    ]);
});

test('admin can view class participants', function () {
    $participant = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->gymClass->participants()->attach($participant);

    Livewire::actingAs($this->admin)
        ->test(ClassShow::class, ['gymClass' => $this->gymClass])
        ->assertSee($participant->name);
});

test('admin can check in a participant', function () {
    $participant = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->gymClass->participants()->attach($participant);

    Livewire::actingAs($this->admin)
        ->test(ClassShow::class, ['gymClass' => $this->gymClass])
        ->call('checkInParticipant', $participant->id);

    expect($this->gymClass->participants()->find($participant->id)->pivot->checked_at)->not->toBeNull();
});

test('admin can add a trial booking', function () {
    Livewire::actingAs($this->admin)
        ->test(ClassShow::class, ['gymClass' => $this->gymClass])
        ->set('trialName', 'John Doe')
        ->call('addTrial');

    expect(GymClassTrial::where('gym_class_id', $this->gymClass->id)->count())->toBe(1);
    expect(GymClassTrial::where('gym_class_id', $this->gymClass->id)->first()->name)->toBe('John Doe');
});

test('admin can check in a trial participant', function () {
    $trial = GymClassTrial::factory()->create([
        'gym_class_id' => $this->gymClass->id,
        'tenant_id' => $this->tenant->id,
        'name' => 'Jane Doe',
    ]);

    Livewire::actingAs($this->admin)
        ->test(ClassShow::class, ['gymClass' => $this->gymClass])
        ->call('checkInTrial', $trial->id);

    expect($trial->fresh()->checked_at)->not->toBeNull();
});

test('admin can book on behalf of another user', function () {
    $otherUser = User::factory()->create(['tenant_id' => $this->tenant->id]);

    Livewire::actingAs($this->admin)
        ->test(ClassShow::class, ['gymClass' => $this->gymClass])
        ->set('userSearch', $otherUser->email)
        ->call('selectUser', $otherUser->id)
        ->call('addParticipant');

    expect($this->gymClass->participants()->where('user_id', $otherUser->id)->exists())->toBeTrue();
});
