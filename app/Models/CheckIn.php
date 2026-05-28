<?php

namespace App\Models;

use App\Events\UserCheckedIn;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckIn extends Model
{
    use BelongsToTenant, HasFactory;

    protected $dispatchesEvents = [
        'created' => UserCheckedIn::class,
    ];

    protected $fillable = [
        'user_id',
        'tenant_id',
        'is_paid',
        'charge_id',
        'checked_at',
        'gym_class_id',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'checked_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function gymClass(): BelongsTo
    {
        return $this->belongsTo(GymClass::class);
    }
}
