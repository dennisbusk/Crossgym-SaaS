<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\RoleObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

#[ObservedBy([RoleObserver::class])]
class Role extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $fillable = [
        'name',
        'slug',
        'tenant_id',
    ];

    protected array $translatable = ['name'];

    protected static function booted()
    {
        static::addGlobalScope('exclude_superadmin', function (Builder $builder) {
            $builder->where('slug', '!=', 'superadmin');
        });
    }

    /**
     * A role has many users.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Many-to-many relation to permissions (new system).
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class)->withTimestamps();
    }

    /**
     * Check if role has a specific permission.
     */
    public function hasPermission(string $model, string $ability): bool
    {
        return $this->permissions()
            ->where('model', $model)
            ->where('ability', $ability)
            ->exists();
    }

    /**
     * The tenant this role belongs to.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeVisibleFor(Builder $query, $role = 'member')
    {
        if ($role !== 'superadmin') {
            $query->where('slug', '!=', 'superadmin');
        }
    }

    public function scopeWithGlobalRoles($query): void
    {
        $tenantId = tenant()->id ?? null;
        $query->withoutGlobalScope('tenant')
            ->where(function ($q) use ($tenantId) {
                if ($tenantId) {
                    $q->where('tenant_id', $tenantId)
                        ->orWhereNull('tenant_id');
                } else {
                    $q->whereNull('tenant_id');
                }
            });
    }
}
