<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GymClassTrial extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'gym_class_id',
        'name',
        'check_in_id',
    ];

    public function gymClass(): BelongsTo
    {
        return $this->belongsTo(GymClass::class);
    }

    public function checkIn(): BelongsTo
    {
        return $this->belongsTo(CheckIn::class);
    }

    public function getCheckedAtAttribute()
    {
        return $this->checkIn?->checked_at;
    }

    public function getIsCheckedInAttribute(): bool
    {
        return $this->checkIn !== null && $this->checkIn->checked_at !== null;
    }
}
