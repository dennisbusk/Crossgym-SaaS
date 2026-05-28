<div class="space-y-6 p-6 bg-neutral-950 min-h-screen text-white">
    <x-banners/>

    @if($subscriptionNotice)
        <div class="rounded-xl border border-yellow-300/30 bg-yellow-400/10 p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="font-semibold text-yellow-200">
                        {{ __('Your subscription requires attention. Please complete payment to activate your membership.') }}
                    </div>
                    <div class="text-xs text-yellow-300/70 mt-1">
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

    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold">{{ __('Dashboard') }}</h1>
            <p class="text-neutral-400 text-sm">{{ __('Oversigt over dit center') }}</p>
        </div>
        <div class="flex items-center gap-4">
            @can('view_export', $dashboard)
                <flux:button wire:click="export" variant="ghost" icon="document-arrow-down" class="text-neutral-400 hover:text-white">{{ __('Export') }}</flux:button>
            @endcan
            @if((auth()->user()?->can('view_revenue', $dashboard) && (auth()->user()->dashboard_settings['revenue'] ?? true))
             || (auth()->user()?->can('view_bookings', $dashboard) && (auth()->user()->dashboard_settings['bookings'] ?? true))
              || (auth()->user()?->can('view_charts', $dashboard) && (auth()->user()->dashboard_settings['charts'] ?? true)))
                <flux:select wire:model.live="period" class="min-w-[140px]">
                    <flux:select.option value="today">{{ __('Today') }}</flux:select.option>
                    <flux:select.option value="week">{{ __('This week') }}</flux:select.option>
                    <flux:select.option value="month">{{ __('This month') }}</flux:select.option>
                    <flux:select.option value="quarter">{{ __('This quarter') }}</flux:select.option>
                    <flux:select.option value="year">{{ __('This year') }}</flux:select.option>
                </flux:select>
            @endif

            @can('view_stripe_status', $dashboard)
                @php($tenant = tenant())
                @if(auth()->check() && $tenant && !($tenant->stripe_connect_onboarded))
                    <flux:button href="{{ route('stripe.connect.start') }}" variant="primary">
                        {{ __('Forbind med Stripe') }}
                    </flux:button>
                @elseif($tenant && $tenant->stripe_connect_onboarded)
                    <flux:badge color="green" size="sm" inset="top bottom">{{ __('Stripe er forbundet!') }}</flux:badge>
                @endif
            @endcan

            <flux:button variant="primary" icon="plus" href="{{ route('calendar') }}">{{ __('Tilføj ny') }}</flux:button>
        </div>
    </div>

    <!-- Stats Top Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-4">
        <div class="bg-neutral-900 border border-neutral-800 rounded-xl p-4">
            <div class="text-neutral-500 text-xs font-medium uppercase tracking-wider">{{ __('Total medlemmer') }}</div>
            <div class="text-2xl font-bold mt-1">{{ ($membershipStatus['active'] ?? 0) + ($membershipStatus['trial'] ?? 0) }}</div>
            <div class="text-neutral-500 text-xs mt-1">{{ $membershipStatus['active'] ?? 0 }} {{ __('aktive') }} · {{ $membershipStatus['trial'] ?? 0 }} {{ __('prøve') }}</div>
        </div>

        @if(auth()->user()->can('view_revenue', $dashboard) && (auth()->user()->dashboard_settings['revenue'] ?? true))
        <div class="bg-neutral-900 border border-neutral-800 rounded-xl p-4">
            <div class="text-neutral-500 text-xs font-medium uppercase tracking-wider">{{ __('Total Revenue (DKK)') }}</div>
            <div class="text-2xl font-bold mt-1">{{ number_format($totalRevenueDkk / 100, 2, ',', '.') }}</div>
            <div class="text-neutral-500 text-xs mt-1">{{ __('Total Transactions') }}: {{ $totalTransactions }}</div>
        </div>
        @endif

        @if(auth()->user()->can('view_bookings', $dashboard) && (auth()->user()->dashboard_settings['bookings'] ?? true))
        <div class="bg-neutral-900 border border-neutral-800 rounded-xl p-4">
            <div class="text-neutral-500 text-xs font-medium uppercase tracking-wider">{{ __('Aktive bookinger') }}</div>
            <div class="text-2xl font-bold mt-1">{{ number_format($totalBookingsActive) }}</div>
            <div class="text-neutral-500 text-xs mt-1">{{ __('Fremtidige hold') }}</div>
        </div>
        <div class="bg-neutral-900 border border-neutral-800 rounded-xl p-4">
            <div class="text-neutral-500 text-xs font-medium uppercase tracking-wider">{{ __('Gennemførte') }}</div>
            <div class="text-2xl font-bold mt-1">{{ number_format($totalBookingsCompleted) }}</div>
            <div class="text-neutral-500 text-xs mt-1">{{ __('Check-ins i perioden') }}</div>
        </div>
        @endif

        <div class="bg-neutral-900 border border-neutral-800 rounded-xl p-4">
            <div class="text-neutral-500 text-xs font-medium uppercase tracking-wider">{{ __('Medlemsstatus') }}</div>
            <div class="flex gap-2 mt-2">
                <flux:badge color="red" size="sm">{{ $membershipStatus['expired'] ?? 0 }} {{ __('udløbet') }}</flux:badge>
                <flux:badge color="yellow" size="sm">{{ $membershipStatus['frozen'] ?? 0 }} {{ __('frosset') }}</flux:badge>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-6">
        <!-- Main Section -->
        <div class="col-span-12 lg:col-span-8 space-y-6">
            <!-- Check-ins Chart -->
            <div class="bg-neutral-900 border border-neutral-800 rounded-xl p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="font-semibold">{{ __('Check-ins over tid') }}</h3>
                </div>
                <div class="h-80" id="checkins-chart-container" data-checkins="{{ json_encode($checkInsChartData) }}">
                    <canvas id="checkInsChart"></canvas>
                </div>
            </div>

            <!-- Recent Activity / Bookings Table -->
            <div class="bg-neutral-900 border border-neutral-800 rounded-xl p-6 overflow-hidden">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-semibold">{{ __('Seneste aktivitet') }}</h3>
                    <flux:button variant="ghost" size="sm" href="{{ route('payments.index') }}">{{ __('Se alle') }}</flux:button>
                </div>
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Medlem') }}</flux:table.column>
                        <flux:table.column>{{ __('Type') }}</flux:table.column>
                        <flux:table.column>{{ __('Beløb') }}</flux:table.column>
                        <flux:table.column>{{ __('Dato') }}</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($recentActivity as $activity)
                        <flux:table.row>
                            <flux:table.cell>{{ $activity->user->name ?? __('Unknown') }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="neutral" inset="top bottom">{{ $activity->type }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ number_format($activity->amount / 100, 2, ',', '.') }} {{ $activity->currency }}</flux:table.cell>
                            <flux:table.cell class="text-neutral-400 text-sm">{{ $activity->created_at?->diffForHumans() }}</flux:table.cell>
                        </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>
        </div>

        <!-- Sidebar Section -->
        <div class="col-span-12 lg:col-span-4 space-y-6">
            <!-- Popular Classes -->
            <div class="bg-neutral-900 border border-neutral-800 rounded-xl p-6">
                <h3 class="font-semibold mb-4">{{ __('Populære hold') }}</h3>
                <div class="space-y-4">
                    @forelse($popularClasses as $index => $class)
                    <div class="space-y-1">
                        <div class="flex justify-between text-sm">
                            <span class="font-medium text-neutral-200">{{ $index + 1 }}. {{ $class->name }}</span>
                            <span class="text-neutral-500 text-xs">{{ $class->bookings }} {{ __('bookinger') }}</span>
                        </div>
                        <div class="w-full bg-neutral-800 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-blue-500 h-1.5 rounded-full" style="width: {{ $class->occupancy }}%"></div>
                        </div>
                    </div>
                    @empty
                        <p class="text-neutral-500 text-sm italic">{{ __('Ingen data tilgængelig') }}</p>
                    @endforelse
                </div>
            </div>

            <!-- Todays Schedule -->
            <div class="bg-neutral-900 border border-neutral-800 rounded-xl p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-semibold">{{ __('Dagens program') }}</h3>
                    <flux:button variant="ghost" size="sm" icon="calendar" href="{{ route('calendar') }}" />
                </div>
                <div class="space-y-4">
                    @forelse($todaysSchedule as $class)
                    <div class="flex items-start gap-4 p-2 rounded-lg hover:bg-neutral-800 transition">
                        <span class="text-xs font-mono text-blue-400 mt-1">{{ $class->class_start->format('H:i') }}</span>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-semibold truncate">{{ $class->getTranslation('name', app()->getLocale()) }}</div>
                            <div class="text-xs text-neutral-500 truncate">{{ __('Træner') }}: {{ $class->trainer->name }}</div>
                        </div>
                        @if($class->class_start->isPast() && $class->class_end->isFuture())
                            <flux:badge size="sm" color="blue" inset="top bottom">{{ __('LIVE') }}</flux:badge>
                        @endif
                    </div>
                    @empty
                        <p class="text-neutral-500 text-sm italic text-center py-4">{{ __('Ingen hold i dag') }}</p>
                    @endforelse
                </div>
            </div>

            <!-- Revenue Chart (Secondary) -->
            <div class="bg-neutral-900 border border-neutral-800 rounded-xl p-6">
                <h3 class="font-semibold mb-3">{{ __('Omsætning') }}</h3>
                <div class="h-48" id="revenue-chart-container" data-revenue="{{ json_encode($revenueChartData) }}">
                    <canvas id="dashboard-revenue-chart"></canvas>
                </div>
            </div>

            <!-- Subscribers by Plan -->
            @if(auth()->user()->can('view_subscribers', $dashboard) && (auth()->user()->dashboard_settings['subscribers'] ?? true))
            <div class="bg-neutral-900 border border-neutral-800 rounded-xl p-6">
                <h3 class="font-semibold mb-4">{{ __('Total Subscribers (per Plan)') }}</h3>
                <div class="space-y-3">
                    @forelse($subscribersByPlan as $plan => $total)
                        <div class="flex items-center justify-between p-2 rounded bg-neutral-800/50 border border-neutral-700/30">
                            <span class="text-sm text-neutral-300">{{ $plan }}</span>
                            <span class="font-bold text-blue-400">{{ $total }}</span>
                        </div>
                    @empty
                        <p class="text-neutral-500 text-sm italic text-neutral-500">{{ __('No subscribers yet.') }}</p>
                    @endforelse
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Training Dashboard Widgets -->
    @if(auth()->user()->dashboard_settings['training_dashboard'] ?? true)
    <div class="mt-12 pt-12 border-t border-neutral-800">
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
            <div class="rounded-xl border-2 border-dashed border-neutral-800 p-12 text-center">
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
                <div class="w-full md:w-1/2 lg:w-1/3 xl:w-1/4">
                    <livewire:components.recovery-score-widget />
                </div>
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

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        function initDashboardCharts() {
            const checkinsContainer = document.getElementById('checkins-chart-container');
            const revenueContainer = document.getElementById('revenue-chart-container');

            if (!checkinsContainer || !revenueContainer) return;

            const checkinsData = JSON.parse(checkinsContainer.dataset.checkins || '{"labels":[],"data":[]}');
            const revenueData = JSON.parse(revenueContainer.dataset.revenue || '{"labels":[],"data":[]}');

            const checkinsCtx = document.getElementById('checkInsChart');
            const revenueCtx = document.getElementById('dashboard-revenue-chart');

            if (!checkinsCtx || !revenueCtx) return;

            const textColor = '#a1a1aa';
            const gridColor = '#262626';

            if (window.checkinsChart) window.checkinsChart.destroy();
            if (window.revenueChart) window.revenueChart.destroy();

            window.checkinsChart = new Chart(checkinsCtx, {
                type: 'bar',
                data: {
                    labels: checkinsData.labels,
                    datasets: [{
                        label: '{{ __("Check-ins") }}',
                        data: checkinsData.data,
                        backgroundColor: '#3b82f6',
                        borderRadius: 4,
                        hoverBackgroundColor: '#60a5fa'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { ticks: { color: textColor }, grid: { display: false } },
                        y: { ticks: { color: textColor }, grid: { color: gridColor } }
                    }
                }
            });

            window.revenueChart = new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: revenueData.labels,
                    datasets: [{
                        label: '{{ __("Revenue") }}',
                        data: revenueData.data,
                        borderColor: '#22c55e',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { display: false },
                        y: { display: false }
                    }
                }
            });
        }

        document.addEventListener('livewire:navigated', initDashboardCharts);
        document.addEventListener('DOMContentLoaded', initDashboardCharts);
        window.addEventListener('charts-updated', initDashboardCharts);
    </script>
    @endpush
</div>
