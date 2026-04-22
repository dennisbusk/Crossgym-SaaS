<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Color extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = ['tenant_id', 'color', 'name'];

    public function classes(): HasMany
    {
        return $this->hasMany(GymClass::class, 'color_id');
    }
}
