<?php

use App\Models\Challenge;
use App\Models\CheckIn;
use App\Models\Exercise;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WorkoutLog;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->role = Role::factory()->create(['slug' => 'member']);
    $this->user = User::factory()->create([
        'tenant_id' => $this->tenant->id,
        'role_id' => $this->role->id,
        'recovery_score' => 85,
    ]);
    $this->token = JWTAuth::fromUser($this->user);
});

it('can get user recovery score', function () {
    $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->getJson('/api/v1/user/recovery');

    $response->assertStatus(200)
        ->assertJson([
            'score' => 85,
            'status' => 'green',
        ])
        ->assertJsonStructure(['score', 'status', 'message', 'insights']);
});

it('can get ai suggestions', function () {
    $exercise = Exercise::factory()->create(['tenant_id' => $this->tenant->id]);
    WorkoutLog::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->user->id,
        'exercise_id' => $exercise->id,
        'weight' => 100,
        'reps' => 5,
        'date' => now()->subDay(),
    ]);

    $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->getJson('/api/v1/ai/suggestions');

    $response->assertStatus(200)
        ->assertJsonStructure(['type', 'exercise_id', 'suggestion']);

    expect($response->json('type'))->toBe('progression');
    expect($response->json('exercise_id'))->toBe($exercise->id);
});

it('can list challenges', function () {
    Challenge::factory()->create([
        'tenant_id' => $this->tenant->id,
        'name' => ['da' => 'Challenge 1'],
        'is_active' => true,
    ]);

    $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->getJson('/api/v1/challenges');

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.title'))->toBe('Challenge 1');
});

it('can react to activity feed item', function () {
    $checkIn = CheckIn::factory()->create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->user->id,
    ]);

    $activityId = 'checkin_' . $checkIn->id;

    $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->postJson("/api/v1/dashboard/activity-feed/{$activityId}/react", [
            'type' => 'fist_bump'
        ]);

    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    $this->assertDatabaseHas('fist_bumps', [
        'user_id' => $this->user->id,
        'bumpable_type' => CheckIn::class,
        'bumpable_id' => $checkIn->id,
    ]);
});
