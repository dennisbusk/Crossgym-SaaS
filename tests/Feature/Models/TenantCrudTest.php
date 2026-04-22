<?php

declare(strict_types=1);

use App\Models\Tenant;

it('can create a tenant', function () {
    $tenant = Tenant::factory()->create();

    expect($tenant->exists)->toBeTrue()
        ->and(Tenant::count())->toBe(1);
});

it('can read a tenant', function () {
    $tenant = Tenant::factory()->create();

    $found = Tenant::find($tenant->id);

    expect($found)->not->toBeNull()
        ->and($found->id)->toBe($tenant->id);
});

it('can update a tenant', function () {
    $tenant = Tenant::factory()->create(['name' => 'Acme']);

    $tenant->update(['name' => 'Acme Updated']);

    expect($tenant->fresh()->name)->toBe('Acme Updated');
});

it('can delete a tenant', function () {
    $tenant = Tenant::factory()->create();

    $tenant->delete();

    expect(Tenant::find($tenant->id))->toBeNull();
});
