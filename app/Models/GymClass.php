<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Translatable\HasTranslations;

class GymClass extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $table = 'classes';

    protected array $translatable = ['name', 'description'];

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'trainer_id',
        'class_type_id',
        'max_participants',
        'class_start',
        'class_end',
        'recurring_id',
        'all_day_event',
        'featured',
        'color_id',
    ];

    protected $casts = [
        'name' => AsArrayObject::class,
        'description' => AsArrayObject::class,
        'class_start' => 'datetime',
        'class_end' => 'datetime',
        'all_day_event' => 'boolean',
        'featured' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function classType(): BelongsTo
    {
        return $this->belongsTo(ClassType::class, 'class_type_id');
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class, 'color_id');
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    /**
     * Participants enrolled in this class.
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'gym_class_user', 'gym_class_id', 'user_id')
            ->using(\App\Models\Pivots\GymClassUser::class)
            ->withPivot(['id', 'check_in_id'])
            ->withTimestamps();
    }

    public function trials(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(GymClassTrial::class);
    }
}
