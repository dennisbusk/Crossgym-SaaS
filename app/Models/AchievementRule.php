<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AchievementRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'achievement_id',
        'event',
        'operator',
        'target',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function achievement(): BelongsTo
    {
        return $this->belongsTo(Achievement::class);
    }
}
