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
        <div class="flex items-center gap-2">
            <div x-data="{ canInstall: false }"
                 @pwa-installable.window="canInstall = true"
                 @pwa-installed.window="canInstall = false"
                 x-show="canInstall">
                <flux:button icon="arrow-down-tray"
                             variant="ghost"
                             @click="installPwa()"
                             aria-label="{{ __('Install App') }}">
                    <span class="max-sm:hidden" id="pwa-install-label">{{ __('Install') }}</span>
                </flux:button>
            </div>
            <x-theme-toggle/>
        </div>
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
    <flux:navlist.item icon="trophy" :href="route('challenges.index')" :current="request()->routeIs('challenges.*')" wire:navigate>
        {{ __('Challenges') }}
    </flux:navlist.item>
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

        @can('viewAny', \App\Models\Achievement::class)
            <flux:navlist.item icon="star" :href="route('achievements.index')" :current="request()->routeIs('achievements.*')" wire:navigate>
                {{ __('Achievements') }}
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

    <div x-data="{ canInstall: false }"
         @pwa-installable.window="canInstall = true"
         @pwa-installed.window="canInstall = false"
         x-show="canInstall"
         class="lg:hidden px-4 py-3 bg-zinc-100 dark:bg-zinc-800 border-t border-zinc-200 dark:border-zinc-700">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-white dark:bg-zinc-700 rounded-lg shadow-sm">
                    <x-app-logo class="size-8" />
                </div>
                <div>
                    <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Add to Home Screen') }}</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Install for a better experience') }}</p>
                </div>
            </div>
            <flux:button variant="primary" size="sm" @click="installPwa()">
                {{ __('Install') }}
            </flux:button>
        </div>
    </div>

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

    <div x-data="{ canInstall: false }"
         @pwa-installable.window="canInstall = true"
         @pwa-installed.window="canInstall = false"
         x-show="canInstall"
         class="flex items-center me-2">
        <flux:button icon="arrow-down-tray"
                     variant="ghost"
                     @click="installPwa()"
                     aria-label="{{ __('Install App') }}" />
    </div>

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

    <!-- Global Toast Notifications -->
    <div
        x-data="{
            notifications: [],
            add(e) {
                const id = Date.now();
                const content = typeof e.detail === 'string' ? e.detail : (e.detail.message || e.detail[0]?.message || 'Success');
                const type = e.detail.type || 'success';

                this.notifications.push({ id, content, type });
                setTimeout(() => this.remove(id), 5000);
            },
            remove(id) {
                this.notifications = this.notifications.filter(n => n.id !== id);
            }
        }"
        @notify.window="add($event)"
        class="fixed top-4 right-4 z-[100] flex flex-col gap-2 pointer-events-none"
    >
        <template x-for="notification in notifications" :key="notification.id">
            <div
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-x-8"
                x-transition:enter-end="opacity-100 transform translate-x-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform translate-x-0"
                x-transition:leave-end="opacity-0 transform translate-x-8"
                class="pointer-events-auto p-4 rounded-lg shadow-lg border flex items-center gap-3 min-w-[300px] max-w-md"
                :class="{
                    'bg-green-100 border-green-200 text-green-800 dark:bg-green-900 dark:border-green-800 dark:text-green-100': notification.type === 'success',
                    'bg-red-100 border-red-200 text-red-800 dark:bg-red-900 dark:border-red-800 dark:text-red-100': notification.type === 'error',
                    'bg-blue-100 border-blue-200 text-blue-800 dark:bg-blue-900 dark:border-blue-800 dark:text-blue-100': notification.type === 'info'
                }"
            >
                <div x-show="notification.type === 'success'" class="shrink-0 text-green-500">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                </div>
                <div x-show="notification.type === 'error'" class="shrink-0 text-red-500">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                </div>
                <div x-show="notification.type === 'info'" class="shrink-0 text-blue-500">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" /></svg>
                </div>
                <div class="text-sm font-medium" x-text="notification.content"></div>
                <button @click="remove(notification.id)" class="ml-auto shrink-0 hover:opacity-75">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>
        </template>
    </div>

</body>
</html>
