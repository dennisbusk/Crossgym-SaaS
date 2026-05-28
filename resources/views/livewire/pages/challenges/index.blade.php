<div class="space-y-6">
    <div class="flex justify-between items-center">
        <flux:heading size="xl">{{ __('Challenges') }}</flux:heading>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($challenges as $challenge)
            @php
                $userChallenge = $challenge->users->first();
                $progress = $userChallenge?->pivot?->current_value ?? 0;
                $percent = min(100, ($progress / max(1, $challenge->goal_value)) * 100);
                $isCompleted = (bool) ($userChallenge?->pivot?->completed_at);
            @endphp
            <flux:card class="space-y-4">
                <div class="flex justify-between items-start">
                    <div>
                        <flux:heading size="lg">{{ $challenge->getTranslation('name', 'da') }}</flux:heading>
                        <flux:subheading>{{ __($challenge->type) }}</flux:subheading>
                    </div>
                    @if($isCompleted)
                        <flux:badge color="green" icon="check">{{ __('Completed') }}</flux:badge>
                    @endif
                </div>

                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $challenge->getTranslation('description', 'da') }}
                </p>

                <div class="space-y-1">
                    <div class="flex justify-between text-xs">
                        <span>{{ __('Progress') }}</span>
                        <span>{{ number_format((float) $progress, 1) }} / {{ number_format((float) $challenge->goal_value, 1) }}</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $percent }}%"></div>
                    </div>
                </div>

                <div class="text-xs text-gray-500">
                    {{ __('Ends') }}: {{ $challenge->end_date ? $challenge->end_date->format('d. M Y') : __('No end date') }}
                </div>
            </flux:card>
        @empty
            <div class="col-span-full text-center py-12">
                <flux:subheading>{{ __('No active challenges found.') }}</flux:subheading>
            </div>
        @endforelse
    </div>
</div>
