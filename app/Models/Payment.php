<?php

declare( strict_types=1 );

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model {

    use HasFactory, BelongsToTenant;

    protected $fillable
        = [
            'tenant_id',
            'user_id',
            'stripe_payment_intent_id',
            'stripe_session_id',
            'amount',
            'currency',
            'status',
            'type',
            'metadata',
        ];

    protected $casts
        = [
            'metadata' => 'array',
        ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function tenant(): BelongsTo {
        return $this->belongsTo(Tenant::class);
    }
}
