<?php

declare(strict_types=1);

use App\Models\AICoachSettings;
use App\Models\Tenant;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $defaults = AICoachSettings::defaults();

        Tenant::query()
            ->whereDoesntHave('aiCoachSettings')
            ->each(function (Tenant $tenant) use ($defaults) {
                AICoachSettings::create([
                    'tenant_id' => $tenant->id,
                    'equipment' => $defaults['equipment'],
                    'intensity' => $defaults['intensity'],
                    'focus_area' => $defaults['focus_area'],
                    'difficulty' => $defaults['difficulty'],
                    'duration_min' => $defaults['duration_min'],
                    'duration_max' => $defaults['duration_max'],
                ]);
            });
    }

    public function down(): void
    {
        // No-op: we don't delete existing settings on rollback
    }
};
