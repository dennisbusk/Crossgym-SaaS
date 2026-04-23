<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subscription extends Model
{
    use HasFactory;
    protected $fillable = [
        'tenant_id', 'user_id', 'stripe_subscription_id', 'stripe_price_id', 'status',
        'current_period_end', 'cancel_at_period_end',
        // extended plan handling
        'plan_type', // 'subscription' | 'one_off'
        'credits_remaining',
        'last_credit_reset_at',
    ];

    protected $casts = [
        'current_period_end' => 'datetime',
        'cancel_at_period_end' => 'boolean',
        'last_credit_reset_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        // Relationship via stripe_price_id rather than a DB foreign key
        return $this->belongsTo(Plan::class, 'stripe_price_id', 'stripe_price_id');
    }

    public function isDayPass(): bool
    {
        return $this->plan?->isDayPass() ?? false;
    }
}
