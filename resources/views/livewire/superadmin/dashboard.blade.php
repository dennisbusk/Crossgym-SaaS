{{--@php /** @var \App\Livewire\SuperAdmin\Dashboard $this */ @endphp--}}
<div class="space-y-6">
    <div class="flex justify-between items-center mb-4">
        <div class="flex justify-self-start">
            <h1 class="text-2xl font-semibold">{{ __('SuperAdmin Dashboard') }}</h1>
        </div>
    </div>
    
    <x-banners/>
    
    <div class="relative overflow-x-auto ">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="rounded border border-neutral-200/80 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-4">
                <div class="text-neutral-500 text-sm">{{ __('Tenants') }}</div>
                <div class="text-3xl font-semibold mt-1">{{ $tenantsCount }}</div>
            </div>
            <div class="rounded border border-neutral-200/80 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-4">
                <div class="text-neutral-500 text-sm">{{ __('Users') }}</div>
                <div class="text-3xl font-semibold mt-1">{{ $usersCount }}</div>
            </div>
            <div class="rounded border border-neutral-200/80 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-4">
                <div class="text-neutral-500 text-sm">{{ __('Plans') }}</div>
                <div class="text-3xl font-semibold mt-1">{{ $plansCount }}</div>
            </div>
            <div class="rounded border border-neutral-200/80 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-4">
                <div class="text-neutral-500 text-sm">{{ __('Subscriptions') }}</div>
                <div class="text-3xl font-semibold mt-1">{{ $subscriptionsCount }}</div>
            </div>
        </div>

        <div class="mt-8 rounded border border-neutral-200/80 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-4">
            <h2 class="text-lg font-semibold mb-4">{{ __('Subscription Overview') }}</h2>
            <div class="relative overflow-x-auto">
                <table class="w-full text-sm text-left rtl:text-right text-neutral-600 dark:text-neutral-300">
                    <thead class="text-xs uppercase bg-neutral-50 dark:bg-neutral-800/50 text-neutral-500 dark:text-neutral-400">
                        <tr>
                            <th class="px-4 py-3">{{ __('Subscription Name') }}</th>
                            <th class="px-4 py-3">{{ __('Number of Tenants') }}</th>
                            <th class="px-4 py-3">{{ __('Earned (DKK)') }}</th>
                            <th class="px-4 py-3 text-right">{{ __('Type') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subscriptionOverview as $row)
                            <tr class="border-b border-neutral-100 dark:border-neutral-800">
                                <td class="px-4 py-3">{{ $row['name'] }}</td>
                                <td class="px-4 py-3">{{ $row['tenants'] }}</td>
                                <td class="px-4 py-3">{{ $row['earned_dkk'] !== null ? number_format($row['earned_dkk'], 2, ',', '.') : __('N/A') }}</td>
                                <td class="px-4 py-3 text-right">{{ \Illuminate\Support\Str::of($row['type'])->replace('_', ' ')->title() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-3 text-neutral-500">{{ __('No data to display.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

{{--    <div class="mt-6">--}}
{{--        <flux:link :href="route('superadmin.settings.general')" variant="primary">{{ __('General settings') }}</flux:link>--}}
{{--    </div>--}}
</div>
</div>
