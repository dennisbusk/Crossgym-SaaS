<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{

    protected $fillable = [
        'tenant_id', 'stripe_price_id', 'name', 'amount', 'currency', 'interval', 'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function subscriptions(): HasMany
    {
        // Relationship via stripe_price_id rather than a DB foreign key
        return $this->hasMany(Subscription::class, 'stripe_price_id', 'stripe_price_id');
    }
}
