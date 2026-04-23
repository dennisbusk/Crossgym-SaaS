<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ $theme['mode'] }}">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-white dark:bg-zinc-800">
@if(function_exists('is_impersonating') && is_impersonating())
    <div class="w-full bg-yellow-500 text-black text-sm px-4 py-2 flex items-center justify-between">
        <div>
            {{ __('You are impersonating') }}: <strong>{{ auth()->user()->name }}</strong>
        </div>
        <a href="{{ route('impersonate.leave') }}" class="underline font-semibold">{{ __('Leave impersonation') }}</a>
    </div>
@endif
<flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
    <flux:sidebar.toggle class="lg:hidden" icon="x-mark"/>
    <div class="flex justify-between">
        <a href="{{ route('home') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
            <x-app-logo/>
        </a>
        <x-theme-toggle/>
    </div>
    @if(function_exists('hasRole') && hasRole('superadmin'))
        @livewire('components.tenant-switcher')
        <flux:navlist.group :heading="__('SuperAdmin')" class="grid" expandable remember>
            @can('viewDashboard', \App\Models\SuperAdmin::class)
                <flux:navlist.item icon="home" :href="route('superadmin.dashboard')" :current="request()->routeIs('superadmin.dashboard')" wire:navigate>{{__('Dashboard')}}</flux:navlist.item>
            @endcan
            @can('viewAny', \App\Models\Tenant::class)
                <flux:navlist.item icon="building-office-2" :href="route('tenants.index')" :current="request()->routeIs('tenants.index')" wire:navigate>{{__('Tenants')}}</flux:navlist.item>
            @endcan
        </flux:navlist.group>
    @endif
    @can('viewAny', \App\Models\Dashboard::class)
        <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
            {{ __('Dashboard') }}
        </flux:navlist.item>
    @endcan
    @can('viewAny', \App\Models\Calendar::class)
        <flux:navlist.item icon="calendar-days" :href="route('calendar')" :current="request()->routeIs('calendar')" wire:navigate>
            {{ __('Calendar') }}
        </flux:navlist.item>
    @endcan

    @can('viewAny', \App\Models\WorkoutLog::class)
        <flux:navlist.item icon="clipboard-document-list" :href="route('workout-logs.index')" :current="request()->routeIs('workout-logs.*')" wire:navigate>
            {{ __('Workout Log') }}
        </flux:navlist.item>
    @endcan
    @can('viewAny', \App\Models\GymClass::class)
        <flux:navlist.item icon="calendar" :href="route('classes.index')" :current="request()->routeIs('classes.index')" wire:navigate>{{__('Classes')}}</flux:navlist.item>
    @endcan
    @can('viewAny', \App\Models\Color::class)
        <flux:navlist.item icon="paint-brush" :href="route('colors.index')" :current="request()->routeIs('colors.index')" wire:navigate>{{__('Colors')}}</flux:navlist.item>
    @endcan
    @can('viewAny', \App\Models\ClassType::class)
        <flux:navlist.item icon="rectangle-stack" :href="route('class-types.index')" :current="request()->routeIs('class-types.index')" wire:navigate>{{__('Class Types')}}</flux:navlist.item>
    @endcan

    <flux:navlist.group
        :heading="__('Admin')"
        class="grid"
        expandable remember
    >

        @can('viewAny', \App\Models\User::class)
            <flux:navlist.item icon="users" :href="route('users.index')" :current="request()->routeIs('users.index')" wire:navigate>{{__('Users')}}</flux:navlist.item>
        @endcan
        @can('viewAny', \App\Models\Role::class)
            <flux:navlist.item icon="academic-cap" :href="route('roles.index')" :current="request()->routeIs('roles.index')" wire:navigate>{{__('Roles')}}</flux:navlist.item>
        @endcan

        @can('viewAny', \App\Models\Plan::class)
            @if(\Illuminate\Support\Facades\Route::has('plans.index'))
                <flux:navlist.item icon="clipboard-document-list" :href="route('plans.index')" :current="request()->routeIs('plans.index')" wire:navigate>{{__('Plans')}}</flux:navlist.item>
            @endif
        @endcan

        @can('viewAny', \App\Models\Subscription::class)
            @if(\Illuminate\Support\Facades\Route::has('subscriptions.index'))
                <flux:navlist.item icon="banknotes" :href="route('subscriptions.index')" :current="request()->routeIs('subscriptions.index')" wire:navigate>{{__('Subscriptions')}}</flux:navlist.item>
            @endif
        @endcan

        @can('viewAny', \App\Models\Payment::class)
            @if(\Illuminate\Support\Facades\Route::has('payments.index'))
                <flux:navlist.item icon="credit-card" :href="route('payments.index')" :current="request()->routeIs('payments.index')" wire:navigate>{{__('Payments')}}</flux:navlist.item>
            @endif
        @endcan

        @can('viewAny', \App\Models\Retention::class)
            <flux:navlist.item icon="exclamation-triangle" :href="route('retention.index')" :current="request()->routeIs('retention.index')" wire:navigate>
                {{ __('Retention') }}
            </flux:navlist.item>
        @endcan
        @can('viewAny', \App\Models\EmailLog::class)
            <flux:navlist.item icon="envelope" :href="route('email-logs.index')" :current="request()->routeIs('email-logs.index')" wire:navigate>
                {{ __('Email Logs') }}
            </flux:navlist.item>
        @endcan

        @can('viewAny', \App\Models\EmailTemplate::class)
            <flux:navlist.item icon="envelope-open" :href="route('admin.email-templates.index')" :current="request()->routeIs('admin.email-templates.*')" wire:navigate>
                {{ __('Email Skabeloner') }}
            </flux:navlist.item>
        @endcan

        @can('viewAny', \App\Models\StripeWebhookLog::class)
            @if(\Illuminate\Support\Facades\Route::has('stripe-webhook-logs.index'))
                <flux:navlist.item icon="arrow-left-end-on-rectangle" :href="route('stripe-webhook-logs.index')" :current="request()->routeIs('stripe-webhook-logs.index')" wire:navigate>{{__('Stripe Webhooks')}}</flux:navlist.item>
            @endif
        @endcan

        @can('viewAny', \App\Models\AICoachSettings::class)
            <flux:navlist.item :href="route('ai-coach-settings.index')" :current="request()->routeIs('ai-coach-settings.index')" icon="sparkles" wire:navigate>{{ __('AI Coach Settings') }}</flux:navlist.item>
        @endcan

        @if(tenant() && auth()->user()->can('update', tenant()))
            <flux:navlist.item icon="cog-6-tooth" :href="route('tenants.edit', tenant())" :current="request()->fullUrlIs(route('tenants.edit', tenant()))" wire:navigate>
                {{ __('Gym Settings') }}
            </flux:navlist.item>
        @endif
    </flux:navlist.group>
    @if(\Illuminate\Support\Facades\Route::has('profile.settings'))
        <flux:navlist.item :href="route('profile.settings')" :current="str_contains(request()->route()->getName(),'profile')" icon="user" wire:navigate>{{ __('Profile') }}</flux:navlist.item>
    @endif


    <flux:spacer/>

    <!-- Desktop User Menu -->
    <flux:dropdown class="hidden lg:block" position="bottom" align="start">
        <flux:profile
            :name="auth()->user()->name"
            :initials="auth()->user()->initials()"
            icon:trailing="chevrons-up-down"
        />

        <flux:menu class="w-[220px]">
            <flux:menu.radio.group>
                <div class="p-0 text-sm font-normal">
                    <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                        <div class="grid flex-1 text-start text-sm leading-tight">
                            <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                            <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                        </div>
                    </div>
                </div>
            </flux:menu.radio.group>

            <flux:menu.separator/>

            <flux:menu.radio.group>
                <flux:menu.item :href="route('profile.settings')" wire:navigate>{{ __('Profile') }}</flux:menu.item>
            </flux:menu.radio.group>

            <flux:menu.separator/>

            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                    {{ __('Log Out') }}
                </flux:menu.item>
            </form>
        </flux:menu>
    </flux:dropdown>
</flux:sidebar>

<!-- Mobile User Menu -->
<flux:header class="lg:hidden">
    <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left"/>

    <flux:spacer/>

    <flux:dropdown position="top" align="end">
        <flux:profile
            :initials="auth()->user()->initials()"
            icon-trailing="chevron-down"
        />

        <flux:menu>
            <flux:menu.radio.group>
                <div class="p-0 text-sm font-normal">
                    <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                        <div class="grid flex-1 text-start text-sm leading-tight">
                            <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                            <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                        </div>
                    </div>
                </div>
            </flux:menu.radio.group>

            <flux:menu.separator/>

            <flux:menu.radio.group>
                <flux:menu.item :href="route('profile.settings')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
            </flux:menu.radio.group>

            <flux:menu.separator/>

            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                    {{ __('Log Out') }}
                </flux:menu.item>
            </form>
        </flux:menu>
    </flux:dropdown>
</flux:header>

{{ $slot }}

@fluxScripts
<script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
@stack('scripts')

</body>
</html>
