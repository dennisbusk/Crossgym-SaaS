<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Challenges;

use App\Models\Challenge;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        $user = Auth::user();
        $challenges = Challenge::where('tenant_id', $user->tenant_id)
            ->where('is_active', true)
            ->with(['users' => fn($q) => $q->where('user_id', $user->id)])
            ->get();

        return view('livewire.pages.challenges.index', [
            'challenges' => $challenges,
        ]);
    }
}
