<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Command;

class SubscriptionsWeeklyReset extends Command
{
    protected $signature = 'subscriptions:weekly-reset';

    protected $description = 'Mark subscriptions weekly reset timestamp for bookkeeping (optional helper)';

    public function handle(): int
    {
        $now = now();
        $weekStart = $now->copy()->startOfWeek();

        $count = Subscription::query()
            ->where('plan_type', 'subscription')
            ->where(function ($q) use ($weekStart) {
                $q->whereNull('last_credit_reset_at')
                    ->orWhere('last_credit_reset_at', '<', $weekStart);
            })
            ->update(['last_credit_reset_at' => $now]);

        $this->info("Updated {$count} subscription rows.");

        return self::SUCCESS;
    }
}
