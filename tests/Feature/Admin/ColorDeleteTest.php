<?php

use App\Livewire\Admin\Colors\ColorIndex;
use App\Models\ClassType;
use App\Models\Color;
use App\Models\GymClass;
use App\Models\Tenant;
use App\Models\User;
use Livewire\Livewire;

it('can delete a color and move its classes to another color', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $classType = ClassType::factory()->create(['tenant_id' => $tenant->id]);

    $color1 = Color::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Color 1']);
    $color2 = Color::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Color 2']);

    $gymClass = GymClass::factory()->create([
        'tenant_id' => $tenant->id,
        'color_id' => $color1->id,
        'class_type_id' => $classType->id,
    ]);

    Livewire::actingAs($user)
        ->test(ColorIndex::class)
        ->call('confirmDelete', $color1->id)
        ->assertSet('deletingColorId', $color1->id)
        ->assertSet('classesCountForDelete', 1)
        ->set('replacementColorId', $color2->id)
        ->call('delete')
        ->assertHasNoErrors();

    expect(Color::find($color1->id))->toBeNull();
    expect($gymClass->refresh()->color_id)->toBe($color2->id);
});

it('can delete a color without moving classes', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $classType = ClassType::factory()->create(['tenant_id' => $tenant->id]);

    $color = Color::factory()->create(['tenant_id' => $tenant->id]);

    $gymClass = GymClass::factory()->create([
        'tenant_id' => $tenant->id,
        'color_id' => $color->id,
        'class_type_id' => $classType->id,
    ]);

    Livewire::actingAs($user)
        ->test(ColorIndex::class)
        ->call('confirmDelete', $color->id)
        ->call('delete')
        ->assertHasNoErrors();

    expect(Color::find($color->id))->toBeNull();
    expect($gymClass->refresh()->color_id)->toBeNull();
});
