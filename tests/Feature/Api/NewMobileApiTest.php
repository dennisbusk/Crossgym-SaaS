<?php

use App\Models\ClassType;
use App\Models\GymClass;
use App\Models\Role;
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
        'name' => ['da' => 'Test Klasse'],
        'description' => ['da' => 'Test Beskrivelse'],
    ]);
});

it('verifies booking cancellation', function () {
    $this->gymClass->participants()->attach($this->user->id);

    $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->deleteJson("/api/v1/gym-classes/{$this->gymClass->id}/book");

    $response->assertStatus(200)
        ->assertJson(['status' => 'success', 'message' => __('Booking cancelled')]);

    expect($this->gymClass->participants()->count())->toBe(0);
});

it('verifies joining waitlist', function () {
    $this->gymClass->update(['max_participants' => 0]); // Force full

    $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->postJson("/api/v1/gym-classes/{$this->gymClass->id}/waitlist");

    $response->assertStatus(200)
        ->assertJsonStructure(['id', 'position', 'estimated_chance']);
});

it('verifies dashboard hero endpoint', function () {
    $this->gymClass->participants()->attach($this->user->id);

    $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->getJson('/api/v1/dashboard/hero');

    $response->assertStatus(200)
        ->assertJsonStructure(['state', 'next_workout' => ['id', 'name', 'class_start', 'trainer']]);
});

it('verifies activity feed endpoint', function () {
    $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->getJson('/api/v1/dashboard/activity-feed');

    $response->assertStatus(200);
    expect($response->json())->toBeArray();
});

it('verifies tenant occupancy endpoint', function () {
    $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->getJson("/api/v1/tenants/{$this->tenant->id}/occupancy");

    $response->assertStatus(200)
        ->assertJsonStructure(['occupancy_percent', 'status', 'current_count', 'capacity']);
});

it('verifies user stats and attendance', function () {
    $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->getJson('/api/v1/users/me/stats');
    $response->assertStatus(200)->assertJsonStructure(['streak_days', 'total_workouts', 'monthly_consistency_percent']);

    $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->getJson('/api/v1/users/me/attendance');
    $response->assertStatus(200)->assertJsonStructure(['data']);
});

it('verifies support endpoints', function () {
    $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->getJson('/api/v1/support/faqs');
    $response->assertStatus(200);
    expect($response->json())->toBeArray();

    $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->postJson('/api/v1/support/tickets', [
            'subject' => 'Test Subject',
            'message' => 'Test Message',
        ]);
    $response->assertStatus(201)->assertJson(['status' => 'success']);
});

it('verifies WOD endpoint', function () {
    $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->getJson("/api/v1/gym-classes/{$this->gymClass->id}/wod");

    $response->assertStatus(200)
        ->assertJsonStructure(['title', 'type', 'description', 'equipment']);
});

it('verifies integration endpoints', function () {
    $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->getJson('/api/v1/membership/wallet-pass');
    $response->assertStatus(501);

    $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->postJson('/api/v1/user/devices/sync', [
            'source' => 'apple_health',
            'data' => [],
        ]);
    $response->assertStatus(200)->assertJson(['status' => 'success']);
});
