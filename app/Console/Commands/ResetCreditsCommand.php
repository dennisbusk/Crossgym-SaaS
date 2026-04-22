<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Command;

class ResetCreditsCommand extends Command
{
    protected $signature = 'credits:reset';

    protected $description = 'Reset subscription credits based on plan limits and billing cycles';

    public function handle()
    {
        $this->info('Starting credit reset...');

        // 1. Weekly Resets (Every Monday)
        if (now()->isMonday()) {
            $this->resetWeeklyCredits();
        }

        // 2. Billing Cycle Resets
        $this->resetBillingCycleCredits();

        $this->info('Credit reset completed.');
    }

    protected function resetWeeklyCredits()
    {
        $now = now();
        $subscriptions = Subscription::where('status', 'active')
            ->where(function ($query) use ($now) {
                $query->whereNull('last_credit_reset_at')
                    ->orWhereDate('last_credit_reset_at', '<', $now->toDateString());
            })
            ->get();

        foreach ($subscriptions as $sub) {
            $plan = $sub->plan;
            if (! $plan) {
                continue;
            }

            $weeklyLimit = $plan->metadata['weekly_limit'] ?? null;
            if ($weeklyLimit !== null) {
                $sub->update([
                    'credits_remaining' => (float) $weeklyLimit,
                    'last_credit_reset_at' => $now,
                ]);
            }
        }
    }

    protected function resetBillingCycleCredits()
    {
        $now = now();
        // Subscriptions where the period just ended
        $subscriptions = Subscription::where('status', 'active')
            ->whereDate('current_period_end', '<=', $now)
            ->where(function ($query) use ($now) {
                $query->whereNull('last_credit_reset_at')
                    ->orWhereDate('last_credit_reset_at', '<', $now->toDateString());
            })
            ->get();

        foreach ($subscriptions as $sub) {
            $plan = $sub->plan;
            if (! $plan) {
                continue;
            }

            $monthlyLimit = $plan->metadata['monthly_limit'] ?? $plan->metadata['total_credits'] ?? null;
            if ($monthlyLimit !== null) {
                $sub->update([
                    'credits_remaining' => (float) $monthlyLimit,
                    'last_credit_reset_at' => $now,
                ]);
            }
        }
    }
}
