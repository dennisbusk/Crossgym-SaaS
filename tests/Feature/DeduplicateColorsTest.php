<?php

declare(strict_types=1);

use App\Models\ClassType;
use App\Models\Color;
use App\Models\GymClass;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;

it('deduplicates colors with the same name and consolidates classes', function () {
    $tenant = Tenant::factory()->create();

    // Opret forudsætninger for GymClass factory
    $role = Role::firstOrCreate(
        ['slug' => 'trainer', 'tenant_id' => $tenant->id],
        ['name' => ['da' => 'Trainer', 'en' => 'Trainer']]
    );
    $trainer = User::factory()->create(['tenant_id' => $tenant->id, 'role_id' => $role->id]);
    $classType = ClassType::factory()->create(['tenant_id' => $tenant->id]);

    // Opret to farver med samme navn
    $color1 = Color::create([
        'tenant_id' => $tenant->id,
        'name' => 'Red',
        'color' => '#FF0000',
    ]);

    $color2 = Color::create([
        'tenant_id' => $tenant->id,
        'name' => 'Red',
        'color' => '#CC0000',
    ]);

    // Opret klasser til begge farver (flest til color 2 for at teste at den vælges som target)
    GymClass::factory()->count(1)->create([
        'tenant_id' => $tenant->id,
        'color_id' => $color1->id,
        'trainer_id' => $trainer->id,
        'class_type_id' => $classType->id,
    ]);

    GymClass::factory()->count(2)->create([
        'tenant_id' => $tenant->id,
        'color_id' => $color2->id,
        'trainer_id' => $trainer->id,
        'class_type_id' => $classType->id,
    ]);

    // Kør kommandoen
    Artisan::call('app:deduplicate-colors');

    // Tjek at der kun er én farve tilbage med navnet 'Red' for denne tenant
    expect(Color::where('name', 'Red')->where('tenant_id', $tenant->id)->count())->toBe(1);

    $remainingColor = Color::where('name', 'Red')->where('tenant_id', $tenant->id)->first();

    // Tjek at det er color2 der blev gemt (fordi den havde flest klasser)
    expect($remainingColor->id)->toBe($color2->id);

    // Tjek at alle 3 klasser nu har den resterende farve
    expect(GymClass::where('color_id', $remainingColor->id)->count())->toBe(3);

    // Tjek at color1 er slettet
    expect(Color::find($color1->id))->toBeNull();
});

it('handles duplicate names correctly even if no classes are attached', function () {
    $tenant = Tenant::factory()->create();

    // To farver uden klasser
    $color1 = Color::create([
        'tenant_id' => $tenant->id,
        'name' => 'Green',
        'color' => '#00FF00',
    ]);

    $color2 = Color::create([
        'tenant_id' => $tenant->id,
        'name' => 'Green',
        'color' => '#00CC00',
    ]);

    Artisan::call('app:deduplicate-colors');

    expect(Color::where('name', 'Green')->where('tenant_id', $tenant->id)->count())->toBe(1);
});

it('does not merge colors from different tenants', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    Color::create([
        'tenant_id' => $tenant1->id,
        'name' => 'Blue',
        'color' => '#0000FF',
    ]);

    Color::create([
        'tenant_id' => $tenant2->id,
        'name' => 'Blue',
        'color' => '#0000FF',
    ]);

    Artisan::call('app:deduplicate-colors');

    expect(Color::where('name', 'Blue')->count())->toBe(2);
});
