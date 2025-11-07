<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionOption extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'active' => 'bool',
        'value' => 'decimal:3',
    ];

    public function tenants(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Tenant::class);
    }
}
