<?php

declare(strict_types=1);

namespace App\Models\Pivots;

use App\Models\CheckIn;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class GymClassUser extends Pivot
{
    /**
     * The table associated with the pivot model.
     *
     * @var string
     */
    protected $table = 'gym_class_user';

    /**
     * Relationship to the VisitLog (CheckIn) record.
     */
    public function checkIn(): BelongsTo
    {
        return $this->belongsTo(CheckIn::class, 'check_in_id');
    }

    /**
     * Helper to verify if the participant has physically arrived.
     */
    public function hasArrived(): bool
    {
        return $this->checkIn !== null && $this->checkIn->checked_at !== null;
    }

    /**
     * Accessor for checked_in_at for backward compatibility or easier access.
     */
    public function getCheckedAtAttribute()
    {
        return $this->checkIn?->checked_at;
    }

    /**
     * Accessor for is_checked_in.
     */
    public function getIsCheckedInAttribute(): bool
    {
        return $this->hasArrived();
    }
}
