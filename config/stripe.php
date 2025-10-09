<?php

declare(strict_types=1);

return [
    // Tenant-aware Stripe configuration. Falls back to services.stripe when tenant() isn't available.
    'key' => function () {
        return tenant()?->stripe_public_key ?? config('services.stripe.key');
    },
    'secret' => function () {
        return tenant()?->stripe_secret_key ?? config('services.stripe.secret');
    },
    'webhook_secret' => function () {
        return tenant()?->stripe_webhook_secret ?? config('services.stripe.webhook_secret');
    },
];
