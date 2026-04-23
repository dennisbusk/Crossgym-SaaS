<div class="space-y-6">
    <x-banners/>

    @if($subscriptionNotice)
        <div class="rounded border border-yellow-300 bg-yellow-50 dark:bg-yellow-900/20 dark:border-yellow-600 p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="font-semibold text-yellow-800 dark:text-yellow-200">
                        {{ __('Your subscription requires attention. Please complete payment to activate your membership.') }}
                    </div>
                    <div class="text-xs text-yellow-700 dark:text-yellow-300 mt-1">
                        {{ __('Status') }}: {{ __($subscriptionNotice['status'] ?? 'N/A') }}
                    </div>
                </div>
                <div class="shrink-0">
                    <flux:button variant="primary" wire:click="completeSubscription">
                        {{ __('Complete subscription') }}
                    </flux:button>
                </div>
            </div>
        </div>
    @endif
@if((auth()->user()?->can('view_revenue', $dashboard) && (auth()->user()->dashboard_settings['revenue'] ?? true))
 || (auth()->user()?->can('view_bookings', $dashboard) && (auth()->user()->dashboard_settings['bookings'] ?? true))
  || (auth()->user()?->can('view_charts', $dashboard) && (auth()->user()->dashboard_settings['charts'] ?? true))
   || auth()->user()?->can('view_stripe_status', $dashboard))
    <div class="flex flex-wrap justify-between items-center gap-4">
{{--        <div class="flex flex-wrap items-center gap-2">--}}
            @if((auth()->user()?->can('view_revenue', $dashboard) && (auth()->user()->dashboard_settings['revenue'] ?? true))
 || (auth()->user()?->can('view_bookings', $dashboard) && (auth()->user()->dashboard_settings['bookings'] ?? true)) || (auth()->user()?->can('view_charts', $dashboard) && (auth()->user()->dashboard_settings['charts'] ?? true)))
                <div>
            <flux:select wire:model.live="period" class="min-w-[140px]">
                <flux:select.option value="today">{{ __('Today') }}</flux:select.option>
                <flux:select.option value="week">{{ __('This week') }}</flux:select.option>
                <flux:select.option value="month">{{ __('This month') }}</flux:select.option>
                <flux:select.option value="quarter">{{ __('This quarter') }}</flux:select.option>
                <flux:select.option value="year">{{ __('This year') }}</flux:select.option>
            </flux:select>
                </div>
            @endif
            @can('view_stripe_status', $dashboard)
            @php($tenant = tenant())
            @if(auth()->check() && $tenant && !($tenant->stripe_connect_onboarded))
                <div>
                <a href="{{ route('stripe.connect.start') }}" class="inline-flex items-center px-4 py-2 bg-primary text-white font-semibold rounded-lg hover:bg-primary/80 transition">
                    {{ __('Forbind med Stripe') }}
                </a>
                </div>
            @elseif($tenant && $tenant->stripe_connect_onboarded)
                <div class="inline-flex text-green-500 font-semibold">
                    {{ __('Stripe er forbundet!') }}
                </div>
            @endif
            @endcan
{{--        </div>--}}
    </div>
@endif
    @if((auth()->user()?->can('view_revenue', $dashboard) && (auth()->user()->dashboard_settings['revenue'] ?? true)) || (auth()->user()?->can('view_bookings', $dashboard) && (auth()->user()->dashboard_settings['bookings'] ?? true)))
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        @if(auth()->user()->can('view_revenue', $dashboard) && (auth()->user()->dashboard_settings['revenue'] ?? true))
        <div class="rounded border border-neutral-200/80 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-4">
            <div class="text-neutral-500 text-sm">{{ __('Total Transactions') }}</div>
            <div class="text-3xl font-semibold mt-1">{{ number_format($totalTransactions) }}</div>
        </div>
        <div class="rounded border border-neutral-200/80 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-4">
            <div class="text-neutral-500 text-sm">{{ __('Total Revenue (DKK)') }}</div>
            <div class="text-3xl font-semibold mt-1">{{ number_format($totalRevenueDkk / 100, 2, ',', '.') }}</div>
        </div>
        @endif
        @if(auth()->user()->can('view_bookings', $dashboard) && (auth()->user()->dashboard_settings['bookings'] ?? true))
        <div class="rounded border border-neutral-200/80 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-4">
            <div class="text-neutral-500 text-sm">{{ __('Total Bookings (Active)') }}</div>
            <div class="text-3xl font-semibold mt-1">{{ number_format($totalBookingsActive) }}</div>
        </div>
        <div class="rounded border border-neutral-200/80 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-4">
            <div class="text-neutral-500 text-sm">{{ __('Total Bookings (Completed)') }}</div>
            <div class="text-3xl font-semibold mt-1">{{ number_format($totalBookingsCompleted) }}</div>
        </div>
        @endif
    </div>
    @endif

    @if(auth()->user()->can('view_subscribers', $dashboard) && (auth()->user()->dashboard_settings['subscribers'] ?? true))
    <div class="rounded border border-neutral-200/80 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-4">
        <h2 class="text-lg font-semibold mb-3">{{ __('Total Subscribers (per Plan)') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            @forelse($subscribersByPlan as $plan => $total)
                <div class="rounded bg-neutral-50 dark:bg-neutral-800 p-3 flex items-center justify-between">
                    <div class="text-neutral-600 dark:text-neutral-300">{{ $plan }}</div>
                    <div class="font-semibold">{{ $total }}</div>
                </div>
            @empty
                <div class="text-neutral-500">{{ __('No subscribers yet.') }}</div>
            @endforelse
        </div>
    </div>
    @endif
    @if(auth()->user()->dashboard_settings['training_dashboard'] ?? true)
    <div class="border-t border-neutral-200 dark:border-neutral-800">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold">{{ __('My Training Dashboard') }}</h2>
            <div class="flex items-center gap-2">
                <flux:dropdown>
                    <flux:button variant="primary" icon="plus" size="sm">{{ __('Add Widget') }}</flux:button>
                    <flux:menu class="min-w-[200px]">
                        <flux:menu.item wire:click="addPrWidget" icon="trophy">{{ __('Personal Records') }}</flux:menu.item>
                        <flux:menu.separator />
                        <flux:menu.heading>{{ __('Exercise Progress') }}</flux:menu.heading>
                        @foreach($availableExercises as $exercise)
                            <flux:menu.item wire:click="addExerciseWidget({{ $exercise->id }})">
                                {{ $exercise->getTranslation('name', app()->getLocale()) }}
                            </flux:menu.item>
                        @endforeach
                    </flux:menu>
                </flux:dropdown>
            </div>
        </div>

        @if($userWidgets->isEmpty())
            <div class="rounded-xl border-2 border-dashed border-neutral-200 dark:border-neutral-800 p-12 text-center">
                <div class="text-neutral-500 mb-4">
                    {{ __('You haven\'t added any training widgets yet.') }}
                </div>
                <flux:dropdown>
                    <flux:button variant="filled">{{ __('Add your first widget') }}</flux:button>
                    <flux:menu class="min-w-[200px]">
                        <flux:menu.item wire:click="addPrWidget" icon="trophy">{{ __('Personal Records') }}</flux:menu.item>
                        <flux:menu.separator />
                        <flux:menu.heading>{{ __('Exercise Progress') }}</flux:menu.heading>
                        @foreach($availableExercises as $exercise)
                            <flux:menu.item wire:click="addExerciseWidget({{ $exercise->id }})">
                                {{ $exercise->getTranslation('name', app()->getLocale()) }}
                            </flux:menu.item>
                        @endforeach
                    </flux:menu>
                </flux:dropdown>
            </div>
        @else
            <div class="flex justify-start flex-wrap gap-6">
                @foreach($userWidgets as $widget)
                    @if(auth()->user()->dashboard_settings['dw_'.$widget->id] ?? true)
                    <div wire:key="widget-{{ $widget->id }}" class="w-full md:w-1/2 lg:w-1/3 xl:w-1/4">
                        @if($widget->type === 'exercise_progress')
                            <livewire:components.exercise-progress-widget :widgetId="$widget->id" :key="'exercise-'.$widget->id" />
                        @elseif($widget->type === 'personal_record')
                            <livewire:components.personal-record-widget :widgetId="$widget->id" :key="'pr-'.$widget->id" />
                        @endif
                    </div>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
    @endif

    @if(auth()->user()->can('view_upcoming_classes', $dashboard) && (auth()->user()->dashboard_settings['upcoming_classes'] ?? true))
    <div class="rounded border border-neutral-200/80 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-4">
        <h2 class="text-lg font-semibold mb-3">{{ __('Upcoming Classes') }}</h2>
        <div class="space-y-2">
            @forelse($upcomingClasses as $class)
                <a href="{{ route('calendar') }}" wire:navigate class="block rounded bg-neutral-50 dark:bg-neutral-800 p-3 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition">
                    <div class="font-medium">{{ $class->hasTranslation('name') ? $class->getTranslation('name', app()->getLocale(), true) : $class->name }}</div>
                    <div class="text-sm text-neutral-500">{{ $class->class_start?->format('d/m H:i') }} · {{ $class->trainer?->name ?? '-' }}</div>
                </a>
            @empty
                <div class="text-neutral-500">{{ __('No upcoming classes.') }}</div>
            @endforelse
        </div>
        @if(count($upcomingClasses) > 0)
        <a href="{{ route('calendar') }}" wire:navigate class="mt-3 inline-block text-sm text-primary hover:underline">{{ __('View calendar') }}</a>
        @endif
    </div>
    @endif

    @if(auth()->user()->can('view_recent_activity', $dashboard) && (auth()->user()->dashboard_settings['recent_activity'] ?? true))
    <div class="rounded border border-neutral-200/80 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-4">
        <h2 class="text-lg font-semibold mb-3">{{ __('Recent Activity') }}</h2>
        <div class="space-y-2">
            @forelse($recentActivity as $payment)
                <div class="rounded bg-neutral-50 dark:bg-neutral-800 p-3 flex justify-between items-center">
                    <div>
                        <span class="font-medium">{{ $payment->user?->name ?? __('Unknown') }}</span>
                        <span class="text-neutral-500 text-sm">· {{ $payment->type }} · {{ number_format($payment->amount / 100, 2, ',', '.') }} {{ $payment->currency }}</span>
                    </div>
                    <div class="text-sm text-neutral-500">{{ $payment->created_at?->diffForHumans() }}</div>
                </div>
            @empty
                <div class="text-neutral-500">{{ __('No recent activity.') }}</div>
            @endforelse
        </div>
    </div>
    @endif

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    @endpush
    @if(auth()->user()->can('view_charts', $dashboard) && (auth()->user()->dashboard_settings['charts'] ?? true))
    <div
        x-data="{
            init() {
                this.initCharts();
            },
            initCharts() {
                if (typeof initDashboardCharts === 'function') {
                    initDashboardCharts();
                }
            }
        }"
        x-on:charts-updated.window="initCharts()"
        class="grid grid-cols-1 lg:grid-cols-2 gap-4"
        id="dashboard-charts-container"
        data-revenue="{{ json_encode($revenueChartData) }}"
        data-bookings="{{ json_encode($bookingsChartData) }}"
        wire:key="charts-{{ $period }}"
    >
        <div class="rounded border border-neutral-200/80 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-4">
            <h2 class="text-lg font-semibold mb-3">{{ __('Revenue (DKK)') }}</h2>
            <div class="h-64">
                <canvas id="dashboard-revenue-chart"></canvas>
            </div>
        </div>
        <div class="rounded border border-neutral-200/80 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-4">
            <h2 class="text-lg font-semibold mb-3">{{ __('Bookings') }}</h2>
            <div class="h-64">
                <canvas id="dashboard-bookings-chart"></canvas>
            </div>
        </div>
    </div>
    @push('scripts')
    <script>
        function initDashboardCharts() {
            const container = document.getElementById('dashboard-charts-container');
            if (!container) return;
            const revenueData = JSON.parse(container.dataset.revenue || '{"labels":[],"data":[]}');
            const bookingsData = JSON.parse(container.dataset.bookings || '{"labels":[],"data":[]}');
            const revenueCtx = document.getElementById('dashboard-revenue-chart');
            const bookingsCtx = document.getElementById('dashboard-bookings-chart');
            if (!revenueCtx || !bookingsCtx) return;
            const isDark = document.documentElement.classList.contains('dark');
            const textColor = isDark ? '#a1a1aa' : '#71717a';
            if (window.revenueChart) window.revenueChart.destroy();
            if (window.bookingsChart) window.bookingsChart.destroy();
            window.revenueChart = new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: revenueData.labels,
                    datasets: [{ label: '{{ __("Revenue") }}', data: revenueData.data, borderColor: '#3b82f6', backgroundColor: 'rgba(59, 130, 246, 0.1)', fill: true }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { ticks: { color: textColor } }, y: { ticks: { color: textColor } } } }
            });
            window.bookingsChart = new Chart(bookingsCtx, {
                type: 'bar',
                data: {
                    labels: bookingsData.labels,
                    datasets: [{ label: '{{ __("Bookings") }}', data: bookingsData.data, backgroundColor: '#22c55e' }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { ticks: { color: textColor } }, y: { ticks: { color: textColor } } } }
            });
        }
        document.addEventListener('livewire:navigated', () => {
            if (typeof initDashboardCharts === 'function') initDashboardCharts();
        });
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof initDashboardCharts === 'function') initDashboardCharts();
        });
    </script>
    @endpush
    @endif

    @if(auth()->user()->can('view_trainer_widget', $dashboard) && (auth()->user()->dashboard_settings['trainer_widget'] ?? true))
    <div class="rounded border border-neutral-200/80 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-4">
        <h2 class="text-lg font-semibold mb-3">{{ __('My Classes Today') }}</h2>
        @if(count($trainerClassesToday) > 0)
        <div class="space-y-2">
            @foreach($trainerClassesToday as $class)
                <a href="{{ route('calendar') }}" wire:navigate class="block rounded bg-neutral-50 dark:bg-neutral-800 p-3 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition">
                    <div class="font-medium">{{ $class->hasTranslation('name') ? $class->getTranslation('name', app()->getLocale(), true) : $class->name }}</div>
                    <div class="text-sm text-neutral-500">{{ $class->class_start?->format('H:i') }} · {{ $class->participants->count() + $class->trials->count() }}/{{ $class->max_participants }} {{ __('participants') }}</div>
                </a>
            @endforeach
        </div>
        @else
            <p class="text-neutral-500 dark:text-neutral-400 text-sm italic">{{ __('No classes scheduled for today.') }}</p>
        @endif
        <a href="{{ route('calendar') }}" wire:navigate class="mt-3 inline-block text-sm text-primary hover:underline">{{ __('Open calendar') }}</a>
    </div>
    @endif

</div>
