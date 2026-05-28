<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Translatable\HasTranslations;

class Challenge extends Model
{
    use BelongsToTenant, HasFactory, HasTranslations;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'type',
        'goal_type',
        'goal_value',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'goal_value' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public array $translatable = ['name', 'description'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['current_value', 'completed_at'])
            ->withTimestamps();
    }
}
