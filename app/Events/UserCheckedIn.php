<?php

namespace App\Events;

use App\Models\CheckIn;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserCheckedIn
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public CheckIn $checkIn
    ) {}
}
