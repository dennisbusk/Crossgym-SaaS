<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessedStripeEvent extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['event_id'];

    protected $primaryKey = 'event_id';
}
