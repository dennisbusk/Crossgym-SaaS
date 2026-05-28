<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Achievement extends Model
{
    use BelongsToTenant, HasFactory, HasTranslations;

    protected $fillable = [
        'tenant_id',
        'slug',
        'name',
        'description',
        'icon',
        'type',
        'category',
        'hidden',
        'repeatable',
        'points',
        'rarity',
        'is_active',
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'hidden' => 'boolean',
        'repeatable' => 'boolean',
        'is_active' => 'boolean',
        'points' => 'integer',
    ];

    public array $translatable = ['name', 'description'];

    public function rules(): HasMany
    {
        return $this->hasMany(AchievementRule::class);
    }

    public function userAchievements(): HasMany
    {
        return $this->hasMany(UserAchievement::class);
    }

    public function userProgress(): HasMany
    {
        return $this->hasMany(UserAchievementProgress::class);
    }
}
