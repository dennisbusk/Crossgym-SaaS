<div class="rounded border border-neutral-200/80 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-4 max-w-[550px] w-full" x-data="{
    initChart() {
        const ctx = document.getElementById('exercise-progress-chart-{{ $this->getId() }}');
        if (!ctx) return;

        const isDark = document.documentElement.classList.contains('dark');
        const textColor = isDark ? '#a1a1aa' : '#71717a';

        if (window.exerciseCharts && window.exerciseCharts['{{ $this->getId() }}']) {
            window.exerciseCharts['{{ $this->getId() }}'].destroy();
        } else if (!window.exerciseCharts) {
            window.exerciseCharts = {};
        }

        window.exerciseCharts['{{ $this->getId() }}'] = new Chart(ctx, {
            type: 'line',
            data: {
                labels: @js($chartData['labels']),
                datasets: [{
                    label: '{{ __('Weight (kg)') }}',
                    data: @js($chartData['data']),
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            color: textColor
                        }
                    },
                    y: {
                        ticks: {
                            color: textColor
                        }
                    }
                }
            }
        });
    }
}" x-init="initChart()" x-on:livewire:commit.window="initChart()">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold">{{ __('Exercise Progress') }}</h2>
        <div class="flex items-center gap-2">
            <flux:select wire:model.live="exerciseId" class="min-w-[150px]">
                <option value="">{{ __('Select exercise') }}</option>
                @foreach($exercises as $exercise)
                    <option value="{{ $exercise->id }}">{{ $exercise->getTranslation('name', app()->getLocale()) }}</option>
                @endforeach
            </flux:select>
            @if($widgetId)
                <flux:button icon="trash" wire:click="remove" variant="ghost" size="sm" />
            @endif
        </div>
    </div>

    <div class="h-64">
        @if(count($chartData['data']) > 0)
            <canvas id="exercise-progress-chart-{{ $this->getId() }}" wire:ignore></canvas>
        @else
            <div class="h-full flex items-center justify-center text-neutral-500">
                {{ __('No data available for the selected exercise.') }}
            </div>
        @endif
    </div>
</div>
