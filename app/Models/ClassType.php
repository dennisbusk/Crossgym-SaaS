<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Spatie\Translatable\HasTranslations;

class ClassType extends Model
{
    use HasFactory;
    use BelongsToTenant;
    use HasTranslations;

    protected $table = 'class_types';
protected array $translatable = [ 'name', 'description'];
    protected $fillable = [
        'tenant_id',
        'color',
        'image',
        'slug',
        'name',
        'description',
    ];

    protected $casts = [
        'name' => AsArrayObject::class,
        'description' => AsArrayObject::class,
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function classes(): HasMany
    {
        return $this->hasMany(GymClass::class, 'class_type_id');
    }
}
