<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Observers\UserObserver;
use App\Traits\BelongsToTenant;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Lab404\Impersonate\Models\Impersonate as ImpersonateTrait;
use Laravel\Fortify\TwoFactorAuthenticatable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

#[ObservedBy([UserObserver::class])]
class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<UserFactory> */
    use BelongsToTenant, HasFactory, ImpersonateTrait, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'tenant_id',
        'medlemsnummer',
        'address',
        'postal_code',
        'city',
        'birthday',
        'phone',
        'mobile',
        'sex',
        'joined_at',
        'left_at',
        'is_approved_for_closed_classes',
        'image',
        'terms_accepted_at',
        'old_user_id',
        'old_member_id',
        'stripe_customer_id',
        'card_brand',
        'card_last_four',
        'dashboard_settings',
        'xp',
        'level',
        'recovery_score',
        'last_hrv',
        'last_rhr',
        'last_sleep_score',
        'recovery_updated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_check_in_at' => 'datetime',
            'joined_at' => 'datetime',
            'left_at' => 'datetime',
            'birthday' => 'date',
            'terms_accepted_at' => 'datetime',
            'dashboard_settings' => 'array',
            'xp' => 'integer',
            'level' => 'integer',
            'recovery_score' => 'integer',
            'last_hrv' => 'integer',
            'last_rhr' => 'integer',
            'last_sleep_score' => 'integer',
            'recovery_updated_at' => 'datetime',
        ];
    }

    protected $with = ['role'];

    /**
     * The role this user belongs to.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class)->withoutGlobalScope('exclude_superadmin')->withGlobalRoles();
    }

    /**
     * The tenant this user belongs to.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }

    public function achievements()
    {
        return $this->belongsToMany(Achievement::class, 'user_achievements')
            ->withPivot('completed_at')
            ->withTimestamps();
    }

    public function achievementProgress()
    {
        return $this->hasMany(UserAchievementProgress::class);
    }

    /**
     * Classes this user participates in.
     */
    public function attendingClasses(): BelongsToMany
    {
        return $this->belongsToMany(GymClass::class, 'gym_class_user', 'user_id', 'gym_class_id')
            ->using(\App\Models\Pivots\GymClassUser::class)
            ->withPivot(['id', 'check_in_id'])
            ->withTimestamps();
    }

    /**
     * Direct user-specific permissions with granted flag.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class)
            ->withPivot('granted')
            ->withTimestamps();
    }

    /**
     * Sync the user's permissions to match the given role's permissions.
     * This overwrites the permission_user table to be a 1:1 reflection of the role.
     */
    public function syncPermissionsFromRole(Role $role): void
    {
        $ids = $role->permissions()->pluck('permissions.id')->all();
        // Build mapping: granted = true for role permissions
        $mapping = [];
        foreach ($ids as $id) {
            $mapping[$id] = ['granted' => true];
        }
        // Replace all existing user permissions with role permissions
        $this->permissions()->sync($mapping);
        // Reload relation cache
        $this->unsetRelation('permissions');
        $this->load('permissions');
    }

    /**
     * Permission check using only the permission_user pivot (granted flag true).
     */
    public function hasPermission(string $model, string $ability): bool
    {
        return $this->permissions()
            ->where('permissions.model', $model)
            ->where('permissions.ability', $ability)
            ->wherePivot('granted', true)
            ->exists();
    }

    /**
     * Whether this user may impersonate others.
     */
    public function canImpersonate(): bool
    {
        // Defer to policy/permissions: allow if user has 'impersonate' on User
        return $this->hasPermission('User', 'impersonate') || ($this->role && $this->role->slug === 'superadmin');
    }

    /**
     * Whether this user can be impersonated by others.
     */
    public function canBeImpersonated(): bool
    {
        // Prevent impersonating SuperAdmin by default
        return ! ($this->role && $this->role->slug === 'superadmin');
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function getLastCheckInAtAttribute()
    {
        return $this->checkIns()->latest()->first()?->checked_at;
    }

    public function checkIns(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CheckIn::class);
    }

    public function dashboardWidgets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserDashboardWidget::class)->orderBy('order');
    }

    public function healthMetrics(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(HealthMetric::class);
    }

    public function challenges(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Challenge::class)
            ->withPivot(['current_value', 'completed_at'])
            ->withTimestamps();
    }

    public function fistBumps(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(FistBump::class);
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
