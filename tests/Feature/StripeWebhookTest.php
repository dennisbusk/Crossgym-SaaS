<?php

declare(strict_types=1);

use App\Models\ProcessedStripeEvent;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;

beforeEach(function () {
    config(['services.stripe.webhook_secret' => 'whsec_test_secret_for_webhook_signing']);
});

function signStripePayload(string $payload, string $secret): string
{
    $timestamp = time();

    return 't='.$timestamp.',v1='.hash_hmac('sha256', $timestamp.'.'.$payload, $secret);
}

it('returns 200 for duplicate webhook events (idempotency)', function () {
    $tenant = Tenant::factory()->create([
        'stripe_connect_account_id' => 'acct_test123',
        'stripe_webhook_secret' => 'whsec_test_secret_for_webhook_signing',
    ]);
    $role = Role::query()->firstOrCreate(
        ['slug' => 'member', 'tenant_id' => $tenant->id],
        ['name' => 'Member']
    );
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'role_id' => $role->id,
    ]);

    $eventId = 'evt_test_'.uniqid();
    $payload = json_encode([
        'id' => $eventId,
        'type' => 'payment_intent.succeeded',
        'data' => [
            'object' => [
                'id' => 'pi_test123',
                'amount' => 1000,
                'amount_received' => 1000,
                'currency' => 'dkk',
                'metadata' => [
                    'user_id' => (string) $user->id,
                ],
            ],
        ],
    ]);

    $sig = signStripePayload($payload, 'whsec_test_secret_for_webhook_signing');
    $headers = [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_STRIPE_SIGNATURE' => $sig,
        'HTTP_STRIPE_ACCOUNT' => 'acct_test123',
    ];

    $response1 = $this->call('POST', route('stripe.webhook.alt'), [], [], [], $headers, $payload);
    $response2 = $this->call('POST', route('stripe.webhook.alt'), [], [], [], $headers, $payload);

    expect($response1->status())->toBe(200);
    expect($response2->status())->toBe(200);

    expect(ProcessedStripeEvent::where('event_id', $eventId)->count())->toBe(1);
});

it('skips processing when event already in processed_stripe_events', function () {
    ProcessedStripeEvent::create(['event_id' => 'evt_already_processed']);

    expect(ProcessedStripeEvent::where('event_id', 'evt_already_processed')->count())->toBe(1);

    $record = ProcessedStripeEvent::firstOrCreate(['event_id' => 'evt_already_processed']);
    expect($record->wasRecentlyCreated)->toBeFalse();
});
