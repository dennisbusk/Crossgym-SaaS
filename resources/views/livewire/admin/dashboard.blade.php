<div class="space-y-6">
    @if (session('status'))
        <div class="rounded-md bg-green-50 p-3 text-green-700">{{ __(session('status')) }}</div>
    @endif

    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold">{{ __('Dashboard') }}</h1>
        <div class="flex items-center gap-2">
            <flux:button wire:click="export" variant="primary">{{ __('Export') }}</flux:button>
            <a href="{{ route('stripe.connect') }}" class="inline-flex items-center px-3 py-2 rounded-md bg-neutral-900 text-white hover:bg-neutral-800">
                {{ __('Connect with Stripe') }}
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="rounded border border-neutral-200/80 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-4">
            <div class="text-neutral-500 text-sm">{{ __('Total Transactions') }}</div>
            <div class="text-3xl font-semibold mt-1">{{ number_format($totalTransactions) }}</div>
        </div>
        <div class="rounded border border-neutral-200/80 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-4">
            <div class="text-neutral-500 text-sm">{{ __('Total Revenue (DKK)') }}</div>
            <div class="text-3xl font-semibold mt-1">{{ number_format($totalRevenueDkk / 100, 2, ',', '.') }}</div>
        </div>
        <div class="rounded border border-neutral-200/80 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-4">
            <div class="text-neutral-500 text-sm">{{ __('Total Bookings (Active)') }}</div>
            <div class="text-3xl font-semibold mt-1">{{ number_format($totalBookingsActive) }}</div>
        </div>
        <div class="rounded border border-neutral-200/80 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-4">
            <div class="text-neutral-500 text-sm">{{ __('Total Bookings (Completed)') }}</div>
            <div class="text-3xl font-semibold mt-1">{{ number_format($totalBookingsCompleted) }}</div>
        </div>
    </div>

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
</div>
