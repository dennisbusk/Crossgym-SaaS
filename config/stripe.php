<?php

declare( strict_types=1 );

return [
    'key'            => function () {
        $tenant = app()->has('tenant') ? app('tenant') : null;

        return $tenant?->stripe_public_key ?? env('STRIPE_PUBLIC_KEY');
    },
    'secret'         => function () {
        $tenant = app()->has('tenant') ? app('tenant') : null;

        return $tenant?->stripe_secret_key ?? env('STRIPE_SECRET_KEY');
    },
    'webhook_secret' => function () {
        $tenant = app()->has('tenant') ? app('tenant') : null;

        return $tenant?->stripe_webhook_secret ?? env('STRIPE_WEBHOOK_SECRET');
    },
];
