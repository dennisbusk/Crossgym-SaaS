<div class="rounded border border-neutral-200/80 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-4 max-w-[550px] w-full">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold">{{ __('Personal Records') }}</h2>
        @if($widgetId)
            <flux:button icon="trash" wire:click="remove" variant="ghost" size="sm" />
        @endif
    </div>

    <div class="space-y-4">
        @if($strengthPrs->isEmpty() && $cardioPrs->isEmpty())
            <div class="text-neutral-500 text-sm italic">
                {{ __('No personal records found.') }}
            </div>
        @endif

        @if($strengthPrs->isNotEmpty())
            <div>
                <h3 class="text-sm font-medium text-neutral-500 uppercase tracking-wider mb-2">{{ __('Strength') }}</h3>
                <div class="grid grid-cols-1 gap-2">
                    @foreach($strengthPrs as $pr)
                        <a href="{{ route('workout-logs.show', $pr) }}" wire:navigate class="flex justify-between items-center p-2 rounded bg-neutral-50 dark:bg-neutral-800 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition group">
                            <div class="flex flex-col">
                                <span class="font-medium group-hover:text-primary transition-colors">{{ $pr->exercise->getTranslation('name', app()->getLocale()) }}</span>
                                <span class="text-xs text-neutral-500">{{ $pr->date->format('d/m/Y') }}</span>
                            </div>
                            <span class="text-primary font-bold">{{ (float) $pr->weight }} kg</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @if($cardioPrs->isNotEmpty())
            <div>
                <h3 class="text-sm font-medium text-neutral-500 uppercase tracking-wider mb-2">{{ __('Cardio') }}</h3>
                <div class="grid grid-cols-1 gap-2">
                    @foreach($cardioPrs as $pr)
                        <a href="{{ route('workout-logs.show', $pr) }}" wire:navigate class="flex justify-between items-center p-2 rounded bg-neutral-50 dark:bg-neutral-800 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition group">
                            <div class="flex flex-col">
                                <span class="font-medium group-hover:text-primary transition-colors">{{ $pr->exercise->getTranslation('name', app()->getLocale()) }}</span>
                                <span class="text-xs text-neutral-500">{{ (float) $pr->distance }} km · {{ $pr->date->format('d/m/Y') }}</span>
                            </div>
                            <span class="text-primary font-bold">
                                {{ floor($pr->duration / 60) }}:{{ str_pad((string)($pr->duration % 60), 2, '0', STR_PAD_LEFT) }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
