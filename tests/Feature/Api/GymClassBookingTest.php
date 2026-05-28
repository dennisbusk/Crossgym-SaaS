<?php

use App\Models\ClassType;
use App\Models\GymClass;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->role = Role::factory()->create(['slug' => 'member']);
    $this->user = User::factory()->create([
        'tenant_id' => $this->tenant->id,
        'role_id' => $this->role->id,
    ]);
    $this->token = JWTAuth::fromUser($this->user);

    $this->classType = ClassType::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->gymClass = GymClass::factory()->create([
        'tenant_id' => $this->tenant->id,
        'class_type_id' => $this->classType->id,
        'max_participants' => 10,
        'class_start' => now()->addHour(),
        'class_end' => now()->addHours(2),
    ]);
});

it('can book a gym class via API', function () {
    Subscription::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->user->id,
        'plan_type' => 'subscription',
        'ends_at' => null,
        'stripe_subscription_id' => 'sub_mock',
        'stripe_price_id' => 'price_mock',
        'status' => 'active',
    ]);

    $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->postJson("/api/v1/gym-classes/{$this->gymClass->id}/book");

    $response->assertStatus(200);
    expect($this->gymClass->participants()->count())->toBe(1);
    expect($this->gymClass->participants()->first()->id)->toBe($this->user->id);
});

it('cannot book if no active subscription', function () {
    $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->postJson("/api/v1/gym-classes/{$this->gymClass->id}/book");

    $response->assertStatus(403);
    $response->assertJson(['message' => __('You need an active subscription to book a class')]);
});

it('cannot book if already booked', function () {
    Subscription::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->user->id,
        'plan_type' => 'subscription',
        'stripe_subscription_id' => 'sub_mock2',
        'stripe_price_id' => 'price_mock2',
        'status' => 'active',
    ]);

    $this->gymClass->participants()->attach($this->user->id);

    $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->postJson("/api/v1/gym-classes/{$this->gymClass->id}/book");

    $response->assertStatus(422);
    $response->assertJson(['message' => __('You are already booked for this class')]);
});

it('cannot book if class is full', function () {
    Subscription::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->user->id,
        'plan_type' => 'subscription',
        'stripe_subscription_id' => 'sub_mock3',
        'stripe_price_id' => 'price_mock3',
        'status' => 'active',
    ]);

    $this->gymClass->update(['max_participants' => 1]);
    $otherUser = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->gymClass->participants()->attach($otherUser->id);

    $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->postJson("/api/v1/gym-classes/{$this->gymClass->id}/book");

    $response->assertStatus(422);
    $response->assertJson(['message' => __('This class is full')]);
});
