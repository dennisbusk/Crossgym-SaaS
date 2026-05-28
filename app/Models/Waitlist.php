<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Waitlist extends Model
{
    use HasFactory;

    protected $table = 'gym_class_waitlist';

    protected $fillable = [
        'gym_class_id',
        'user_id',
    ];

    public function gymClass(): BelongsTo
    {
        return $this->belongsTo(GymClass::class, 'gym_class_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
