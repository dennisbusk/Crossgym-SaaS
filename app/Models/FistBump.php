<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FistBump extends Model
{
    protected $fillable = [
        'user_id',
        'bumpable_id',
        'bumpable_type',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bumpable(): MorphTo
    {
        return $this->morphTo();
    }
}
