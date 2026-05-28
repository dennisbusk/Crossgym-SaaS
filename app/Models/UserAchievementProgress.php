<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAchievementProgress extends Model
{
    use HasFactory;

    protected $table = 'user_achievement_progress';

    protected $fillable = [
        'user_id',
        'achievement_id',
        'progress',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'progress' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function achievement(): BelongsTo
    {
        return $this->belongsTo(Achievement::class);
    }
}
