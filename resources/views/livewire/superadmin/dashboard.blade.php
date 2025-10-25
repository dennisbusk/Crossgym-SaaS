{{--@php /** @var \App\Livewire\SuperAdmin\Dashboard $this */ @endphp--}}
<div class="space-y-6">
    <div class="flex justify-between items-center mb-4">
        <div class="flex justify-self-start">
            <h1 class="text-2xl font-semibold">{{ __('SuperAdmin Dashboard') }}</h1>
        </div>
    </div>
    
    @if (session('status'))
        <div class="rounded-md bg-green-50 p-3 text-green-700">{{ __(session('status')) }}</div>
    @endif
    
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

{{--    <div class="mt-6">--}}
{{--        <flux:link :href="route('superadmin.settings.general')" variant="primary">{{ __('General settings') }}</flux:link>--}}
{{--    </div>--}}
</div>
</div>
