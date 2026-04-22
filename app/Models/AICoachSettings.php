<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AICoachSettings extends Model
{
    protected $table = 'ai_coach_settings';

    protected $fillable = [
        'tenant_id',
        'equipment',
        'intensity',
        'focus_area',
        'difficulty',
        'duration_min',
        'duration_max',
        'ai_provider',
        'ai_api_key',
    ];

    protected $casts = [
        'equipment' => 'array',
        'intensity' => 'array',
        'focus_area' => 'array',
        'difficulty' => 'array',
        'ai_api_key' => 'encrypted',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Default values for new AICoachSettings (most common CrossFit equipment and options).
     */
    public static function defaults(): array
    {
        return [
            'equipment' => [
                'barbell',
                'kettlebell',
                'rower',
                'pull-up bar',
                'jump rope',
                'dumbbells',
                'box',
                'medicine ball',
                'wall ball',
                'rings',
            ],
            'intensity' => ['low', 'medium', 'high'],
            'focus_area' => ['strength', 'conditioning', 'skill', 'mixed'],
            'difficulty' => ['beginner', 'intermediate', 'advanced'],
            'duration_min' => 45,
            'duration_max' => 60,
        ];
    }
}
