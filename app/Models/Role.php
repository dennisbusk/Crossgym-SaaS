<?php

declare( strict_types=1 );

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;
use Str;

class Role extends Model
{
    use HasFactory, BelongsToTenant;
    use HasTranslations;

    protected $fillable = [
        'name',
        'tenant_id',
    ];

    protected static function boot() {
        parent::boot();

        static::creating(function (Role $role ) {
            $role->slug = Str::slug($role->name);
        });
    }

    protected array $translatable = [ 'name' ];


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
    public function permissions(): BelongsToMany {
        return $this->belongsToMany(Permission::class)->withTimestamps();
    }

    /**
     * Check if role has a specific permission.
     */
    public function hasPermission( string $model, string $ability ): bool {
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
}
