<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkoutLog extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'exercise_id',
        'date',
        'weight',
        'reps',
        'sets',
        'distance',
        'duration',
        'intensity',
        'mood',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'weight' => 'decimal:2',
        'distance' => 'decimal:2',
        'reps' => 'integer',
        'sets' => 'integer',
        'duration' => 'integer',
        'intensity' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }
}
