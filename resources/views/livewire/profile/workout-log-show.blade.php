<div class="space-y-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold">{{ __('Workout Log Details') }}</h1>
        <div class="flex items-center gap-2">
            <flux:button icon="pencil-square" href="{{ route('workout-logs.edit', $workoutLog) }}" variant="primary" tag="a">
                {{ __('Edit') }}
            </flux:button>
            <flux:button icon="arrow-left" href="{{ route('workout-logs.index') }}" variant="ghost" tag="a">
                {{ __('Back') }}
            </flux:button>
        </div>
    </div>

    <x-banners/>

    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-lg font-medium mb-4">{{ __('General Information') }}</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-zinc-500">{{ __('Date') }}</dt>
                        <dd class="text-base">{{ $workoutLog->date->format('d.m.Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-zinc-500">{{ __('Exercise') }}</dt>
                        <dd class="text-base">{{ $workoutLog->exercise?->name ?? __('Unknown') }}</dd>
                    </div>
                    @if($workoutLog->exercise)
                    <div>
                        <dt class="text-sm font-medium text-zinc-500">{{ __('Category') }}</dt>
                        <dd class="text-base">{{ __($workoutLog->exercise->category) }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            <div>
                <h3 class="text-lg font-medium mb-4">{{ __('Results') }}</h3>
                <dl class="space-y-3">
                    @if($workoutLog->exercise?->category === 'strength')
                        <div>
                            <dt class="text-sm font-medium text-zinc-500">{{ __('Weight') }}</dt>
                            <dd class="text-base">{{ (float) $workoutLog->weight }} kg</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-zinc-500">{{ __('Reps') }}</dt>
                            <dd class="text-base">{{ $workoutLog->reps }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-zinc-500">{{ __('Sets') }}</dt>
                            <dd class="text-base">{{ $workoutLog->sets }}</dd>
                        </div>
                    @elseif($workoutLog->exercise?->category === 'cardio')
                        <div>
                            <dt class="text-sm font-medium text-zinc-500">{{ __('Distance') }}</dt>
                            <dd class="text-base">{{ (float) $workoutLog->distance }} km</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-zinc-500">{{ __('Duration') }}</dt>
                            <dd class="text-base">{{ floor($workoutLog->duration / 60) }} min {{ $workoutLog->duration % 60 }} sec</dd>
                        </div>
                    @elseif($workoutLog->exercise?->category === 'biometric')
                        <div>
                            <dt class="text-sm font-medium text-zinc-500">{{ __('Weight') }}</dt>
                            <dd class="text-base">{{ (float) $workoutLog->weight }} kg</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-zinc-500">{{ __('Mood') }}</dt>
                            <dd class="text-base">{{ $workoutLog->mood }}</dd>
                        </div>
                    @endif

                    @if($workoutLog->intensity)
                    <div>
                        <dt class="text-sm font-medium text-zinc-500">{{ __('Intensity') }}</dt>
                        <dd class="text-base">{{ $workoutLog->intensity }}/10</dd>
                    </div>
                    @endif
                </dl>
            </div>
        </div>

        @if($workoutLog->notes)
        <div class="mt-8 border-t border-zinc-200 dark:border-zinc-800 pt-6">
            <h3 class="text-lg font-medium mb-2">{{ __('Notes') }}</h3>
            <p class="text-zinc-600 dark:text-zinc-400 whitespace-pre-wrap">{{ $workoutLog->notes }}</p>
        </div>
        @endif
    </div>
</div>
