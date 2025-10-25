<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{

    protected $fillable = [
        'tenant_id', 'user_id', 'stripe_subscription_id', 'stripe_price_id', 'status',
        'current_period_end', 'cancel_at_period_end'
    ];

    protected $casts = [
        'current_period_end' => 'datetime',
        'cancel_at_period_end' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
