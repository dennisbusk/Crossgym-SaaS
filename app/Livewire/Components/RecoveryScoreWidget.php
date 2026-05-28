<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class RecoveryScoreWidget extends Component
{
    public int $score = 0;
    public ?int $hrv = null;
    public ?int $rhr = null;
    public ?int $sleep = null;

    public function mount(): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $this->score = $user->recovery_score ?? 100;
        $this->hrv = $user->last_hrv;
        $this->rhr = $user->last_rhr;
        $this->sleep = $user->last_sleep_score;
    }

    public function render()
    {
        return view('livewire.components.recovery-score-widget');
    }
}
