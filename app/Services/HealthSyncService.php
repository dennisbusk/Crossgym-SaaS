<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\HealthMetric;
use App\Models\User;

class HealthSyncService
{
    public function sync(User $user, array $data): void
    {
        foreach ($data['metrics'] as $metric) {
            HealthMetric::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'type' => $metric['type'],
                    'date' => $metric['date'],
                ],
                [
                    'value' => $metric['value'],
                    'source' => $data['source'] ?? 'api',
                ]
            );
        }

        // Trigger recovery calculation after sync
        app(RecoveryService::class)->calculateForUser($user);
    }
}
