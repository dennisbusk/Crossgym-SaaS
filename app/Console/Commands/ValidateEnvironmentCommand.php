<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ValidateEnvironmentCommand extends Command
{
    protected $signature = 'env:validate
        {--strict : Fail with non-zero exit if any required key is missing}';

    protected $description = 'Validate required environment variables (Stripe keys, etc.)';

    private const REQUIRED = [
        'STRIPE_SECRET' => ['config' => 'services.stripe.secret', 'desc' => 'Stripe API secret key'],
        'STRIPE_WEBHOOK_SECRET' => ['config' => 'services.stripe.webhook_secret', 'desc' => 'Stripe webhook signing secret'],
    ];

    private const OPTIONAL = [
        'STRIPE_KEY' => ['config' => 'services.stripe.key', 'desc' => 'Stripe publishable key (for frontend)'],
        'STRIPE_CONNECT_CLIENT_ID' => ['config' => 'services.stripe.connect_client_id', 'desc' => 'Stripe Connect client ID (for tenant onboarding)'],
        'AI_COACH_STRIPE_PRODUCT_ID' => ['config' => 'services.stripe.ai_coach_product_id', 'desc' => 'Stripe product ID for AI Coach (monthly/yearly prices are fetched from this product)'],
    ];

    public function handle(): int
    {
        $missing = [];
        $optionalMissing = [];

        foreach (self::REQUIRED as $key => $item) {
            if (empty(config($item['config']))) {
                $missing[$key] = $item['desc'];
            }
        }

        foreach (self::OPTIONAL as $key => $item) {
            if (empty(config($item['config']))) {
                $optionalMissing[$key] = $item['desc'];
            }
        }

        if (! empty($missing)) {
            $this->error('Missing required environment variables:');
            foreach ($missing as $key => $desc) {
                $this->line("  - {$key}: {$desc}");
            }
        }

        if (! empty($optionalMissing)) {
            $this->warn('Optional (recommended) environment variables not set:');
            foreach ($optionalMissing as $key => $desc) {
                $this->line("  - {$key}: {$desc}");
            }
        }

        if (empty($missing) && empty($optionalMissing)) {
            $this->info('All environment variables are configured.');

            return self::SUCCESS;
        }

        if (! empty($missing) && $this->option('strict')) {
            return self::FAILURE;
        }

        return empty($missing) ? self::SUCCESS : self::FAILURE;
    }
}
