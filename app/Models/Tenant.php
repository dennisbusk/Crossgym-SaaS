<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'domain',
        'stripe_public_key',
        'stripe_secret_key',
        'stripe_webhook_secret',
    ];

    /**
     * Get the users for the tenant.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
