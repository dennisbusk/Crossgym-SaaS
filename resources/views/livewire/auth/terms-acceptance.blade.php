<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public string $terms = '';

    public function mount()
    {
        $tenant = tenant();
        $this->terms = $tenant?->terms ?? '';

        if (empty($this->terms)) {
            $this->terms = "<h2>§ 1 ".__('Name and domicile')."</h2><p>".__('The name of the association is CrossGym...')."</p>";
        }
    }

    public function accept()
    {
        $user = Auth::user();
        $user->terms_accepted_at = now();
        $user->save();

        return redirect()->intended(route('dashboard'));
    }
}; ?>

<div class="flex flex-col items-center justify-center min-h-screen bg-gray-100 dark:bg-gray-900 p-4">
    <div class="w-full max-w-2xl bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
        <h1 class="text-2xl font-bold mb-4 text-gray-900 dark:text-white">{{ __('Accept Terms & Conditions') }}</h1>

        <div class="prose dark:prose-invert max-w-none mb-6 overflow-y-auto max-h-96 p-4 border rounded">
            {!! $terms !!}
        </div>

        <div class="flex justify-end">
            <flux:button wire:click="accept" variant="primary">
                {{ __('I accept the terms and conditions') }}
            </flux:button>
        </div>
    </div>
</div>
