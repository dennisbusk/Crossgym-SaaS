<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'tenant_id', 'stripe_price_id', 'stripe_product_id', 'name', 'amount', 'currency', 'interval', 'metadata',
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

    public function isDayPass(): bool
    {
        return (bool) ($this->metadata['is_day_pass'] ?? false) || str_contains(strtolower($this->name ?? ''), 'dagskort');
    }
}
