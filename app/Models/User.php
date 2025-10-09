<?php

declare( strict_types=1 );

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Traits\BelongsToTenant;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, BelongsToTenant;

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
        ];
    }

    /**
     * The role this user belongs to.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * The tenant this user belongs to.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Classes this user participates in.
     */
    public function attendingClasses(): BelongsToMany
    {
        return $this->belongsToMany(GymClass::class, 'gym_class_user', 'user_id', 'gym_class_id')->withTimestamps();
    }

    /**
     * Direct user-specific permissions with granted flag.
     */
    public function permissions(): BelongsToMany {
        return $this->belongsToMany(Permission::class)
                    ->withPivot('granted')
                    ->withTimestamps();
    }

    /**
     * Aggregate of role permissions and user overrides.
     */
    public function allPermissions() {
        $rolePermissions = $this->role ? $this->role->permissions?->pluck('id')->toArray() : [];

        $userOverrides = $this->permissions
            ->mapWithKeys(fn( $p ) => [ $p->id => (bool) $p->pivot->granted ])
            ->toArray();

        $final = collect($rolePermissions)
            ->mapWithKeys(fn( $id ) => [ $id => true ])
            ->merge($userOverrides)
            ->filter(fn( $granted ) => $granted)
            ->keys();

        return Permission::whereIn('id', $final)->get();
    }

    public function hasPermission( string $model, string $ability ): bool {
        return $this->allPermissions()
                    ->where('model', $model)
                    ->where('ability', $ability)
                    ->isNotEmpty();
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
}
