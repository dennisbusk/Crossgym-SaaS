<?php

declare(strict_types=1);

use App\Livewire\Components\GymClassCalendar;
use App\Models\ClassType;
use App\Models\Color;
use App\Models\GymClass;
use App\Models\Tenant;
use App\Models\User;
use Livewire\Livewire;

it('dispatches different colors for different class types', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $this->actingAs($user);

    $colorHex1 = '#ff0000';
    $color1 = Color::create([
        'tenant_id' => $tenant->id,
        'name' => 'Red',
        'color' => $colorHex1,
    ]);

    $classType1 = ClassType::factory()->create([
        'tenant_id' => $tenant->id,
    ]);

    $colorHex2 = '#00ff00';
    $color2 = Color::create([
        'tenant_id' => $tenant->id,
        'name' => 'Green',
        'color' => $colorHex2,
    ]);

    $classType2 = ClassType::factory()->create([
        'tenant_id' => $tenant->id,
    ]);

    GymClass::factory()->create([
        'tenant_id' => $tenant->id,
        'class_type_id' => $classType1->id,
        'color_id' => $color1->id,
        'class_start' => now()->startOfDay()->addHours(10),
        'class_end' => now()->startOfDay()->addHours(11),
    ]);

    GymClass::factory()->create([
        'tenant_id' => $tenant->id,
        'class_type_id' => $classType2->id,
        'color_id' => $color2->id,
        'class_start' => now()->startOfDay()->addHours(12),
        'class_end' => now()->startOfDay()->addHours(13),
    ]);

    $start = now()->startOfMonth()->toDateString();
    $end = now()->endOfMonth()->toDateString();

    Livewire::test(GymClassCalendar::class)
        ->call('loadEvents', $start, $end)
        ->assertDispatched('events-updated', function ($name, $params) use ($colorHex1, $colorHex2) {
            $events = $params['events'];
            $foundColor1 = false;
            $foundColor2 = false;

            foreach ($events as $event) {
                if ($event['backgroundColor'] === $colorHex1) {
                    $foundColor1 = true;
                }
                if ($event['backgroundColor'] === $colorHex2) {
                    $foundColor2 = true;
                }
            }

            return $foundColor1 && $foundColor2;
        });
});
