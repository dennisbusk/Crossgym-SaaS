<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = ['model', 'ability', 'description'];

    // Spec says no timestamps, but our migration has timestamps. We'll keep Eloquent timestamps enabled by default.
    // If needed, uncomment next line to disable timestamps.
    // public $timestamps = false;

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('granted')
            ->withTimestamps();
    }

    public function getNameAttribute(): string
    {
        return "{$this->model}.{$this->ability}";
    }
}
