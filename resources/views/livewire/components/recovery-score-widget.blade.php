<flux:card class="space-y-4">
    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('Recovery Status') }}</flux:heading>
        <flux:badge color="{{ $score > 80 ? 'green' : ($score > 50 ? 'yellow' : 'red') }}" variant="pill">
            {{ $score }}%
        </flux:badge>
    </div>

    <div class="flex justify-center py-4">
        <div class="relative size-32">
            <svg class="size-full -rotate-90" viewBox="0 0 36 36" xmlns="http://www.w3.org/2000/svg">
                <!-- Background Circle -->
                <circle cx="18" cy="18" r="16" fill="none" class="stroke-current text-gray-200 dark:text-gray-700" stroke-width="2"></circle>
                <!-- Progress Circle -->
                <circle cx="18" cy="18" r="16" fill="none" class="stroke-current {{ $score > 80 ? 'text-green-500' : ($score > 50 ? 'text-yellow-500' : 'text-red-500') }}" stroke-width="2" stroke-dasharray="100" stroke-dashoffset="{{ 100 - $score }}" stroke-linecap="round"></circle>
            </svg>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-center">
                <span class="text-2xl font-bold">{{ $score }}%</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-2 text-center text-xs">
        <div>
            <div class="text-gray-500">{{ __('HRV') }}</div>
            <div class="font-semibold">{{ $hrv ?? '--' }} ms</div>
        </div>
        <div>
            <div class="text-gray-500">{{ __('RHR') }}</div>
            <div class="font-semibold">{{ $rhr ?? '--' }} bpm</div>
        </div>
        <div>
            <div class="text-gray-500">{{ __('Sleep') }}</div>
            <div class="font-semibold">{{ $sleep ?? '--' }}/100</div>
        </div>
    </div>

    <div class="text-sm text-center text-gray-600 dark:text-gray-400">
        @if($score > 80)
            {{ __('You are ready to push hard today!') }}
        @elseif($score > 50)
            {{ __('Moderate fatigue detected. Consider a steady session.') }}
        @else
            {{ __('High fatigue. Recovery or light mobility recommended.') }}
        @endif
    </div>
</flux:card>
