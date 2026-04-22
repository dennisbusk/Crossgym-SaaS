<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StripeWebhookLog extends Model
{
    protected $fillable = [
        'tenant_id', 'event_type', 'payload', 'processed_at', 'status', 'error',
    ];

    protected $casts = [
        'payload' => 'array',
        'processed_at' => 'datetime',
    ];
}
