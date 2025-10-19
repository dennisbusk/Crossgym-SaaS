<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                <x-app-logo />
            </a>

                    <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="home" :href="route('calendar')" :current="request()->routeIs('calendar')" wire:navigate>
                        {{ __('Calendar') }}
                    </flux:navlist.item>
{{--            <flux:navlist.group :heading="__('Users')" class="grid" expandable remember>--}}
{{--                <flux:navlist.item :href="route('users.index')" :current="request()->routeIs('users.index')" wire:navigate>{{__('Index')}}</flux:navlist.item>--}}
{{--                <flux:navlist.item :href="route('users.create')" :current="request()->routeIs('users.create')" wire:navigate>{{__('Create User')}}</flux:navlist.item>--}}
{{--            </flux:navlist.group>--}}
            @can('viewAny', \App\Models\User::class)
            <flux:navlist.group :heading="__('Users')" class="grid" expandable remember >
                <flux:navlist.item :href="route('users.index')" :current="request()->routeIs('users.index')" wire:navigate>{{__('Index')}}</flux:navlist.item>
                @can('create', \App\Models\User::class)
                <flux:navlist.item :href="route('users.create')" :current="request()->routeIs('users.create')" wire:navigate>{{__('Create User')}}</flux:navlist.item>
                @endcan
            </flux:navlist.group>
            @endcan
            @can('viewAny', \App\Models\GymClass::class)
                <flux:navlist.group :heading="__('Classes')" class="grid" expandable remember>
                    <flux:navlist.item :href="route('classes.index')" :current="request()->routeIs('classes.index')" wire:navigate>{{__('Index')}}</flux:navlist.item>
                    @can('create', \App\Models\GymClass::class)
                        <flux:navlist.item :href="route('classes.create')" :current="request()->routeIs('classes.create')" wire:navigate>{{__('Create Class')}}</flux:navlist.item>
                    @endcan
                </flux:navlist.group>
            @endcan
            @can('viewAny', \App\Models\ClassType::class)
                <flux:navlist.group :heading="__('Class Types')" class="grid" expandable remember>
                    <flux:navlist.item :href="route('class-types.index')" :current="request()->routeIs('class-types.index')" wire:navigate>{{__('Index')}}</flux:navlist.item>
                    @can('create', \App\Models\ClassType::class)
                    <flux:navlist.item :href="route('class-types.create')" :current="request()->routeIs('class-types.create')" wire:navigate>{{__('Create Class Type')}}</flux:navlist.item>
                    @endcan
                </flux:navlist.group>
            @endcan
            @can('viewAny', \App\Models\Role::class)
                <flux:navlist.group :heading="__('Roles')" class="grid" expandable remember>
                    <flux:navlist.item :href="route('roles.index')" :current="request()->routeIs('roles.index')" wire:navigate>{{__('Index')}}</flux:navlist.item>
                    @can('create', \App\Models\Role::class)
                        <flux:navlist.item :href="route('roles.create')" :current="request()->routeIs('roles.create')" wire:navigate>{{__('Create Role')}}</flux:navlist.item>
                    @endcan
                </flux:navlist.group>
            @endcan
            @can('viewAny', \App\Models\Tenant::class)
                <flux:navlist.group
                    :heading="__('Tenants')"
                    class="grid"
                    expandable remember
                >
                    <flux:navlist.item :href="route('tenants.index')" :current="request()->routeIs('tenants.index')" wire:navigate>{{__('Index')}}</flux:navlist.item>
                    @can('create', \App\Models\Tenant::class)
                        <flux:navlist.item :href="route('tenants.create')" :current="request()->routeIs('tenants.create')" wire:navigate>{{__('Create Tenant')}}</flux:navlist.item>
                    @endcan
                </flux:navlist.group>
            @endcan

            @can('viewAny', \App\Models\Plan::class)
                <flux:navlist.group :heading="__('Plans')" class="grid" expandable remember>
                    @if(\Illuminate\Support\Facades\Route::has('plans.index'))
                        <flux:navlist.item :href="route('plans.index')" :current="request()->routeIs('plans.index')" wire:navigate>{{__('Index')}}</flux:navlist.item>
                    @endif
                    @can('create', \App\Models\Plan::class)
                        @if(\Illuminate\Support\Facades\Route::has('plans.create'))
                            <flux:navlist.item :href="route('plans.create')" :current="request()->routeIs('plans.create')" wire:navigate>{{__('Create Plan')}}</flux:navlist.item>
                        @endif
                    @endcan
                </flux:navlist.group>
            @endcan

            @can('viewAny', \App\Models\Subscription::class)
                <flux:navlist.group :heading="__('Subscriptions')" class="grid" expandable remember>
                    @if(\Illuminate\Support\Facades\Route::has('subscriptions.index'))
                        <flux:navlist.item :href="route('subscriptions.index')" :current="request()->routeIs('subscriptions.index')" wire:navigate>{{__('Index')}}</flux:navlist.item>
                    @endif
                    @can('create', \App\Models\Subscription::class)
                        @if(\Illuminate\Support\Facades\Route::has('subscriptions.create'))
                            <flux:navlist.item :href="route('subscriptions.create')" :current="request()->routeIs('subscriptions.create')" wire:navigate>{{__('Create Subscription')}}</flux:navlist.item>
                        @endif
                    @endcan
                </flux:navlist.group>
            @endcan

            @can('viewAny', \App\Models\Payment::class)
                <flux:navlist.group :heading="__('Payments')" class="grid" expandable remember>
                    @if(\Illuminate\Support\Facades\Route::has('payments.index'))
                        <flux:navlist.item :href="route('payments.index')" :current="request()->routeIs('payments.index')" wire:navigate>{{__('Index')}}</flux:navlist.item>
                    @endif
                    @can('create', \App\Models\Payment::class)
                        @if(\Illuminate\Support\Facades\Route::has('payments.create'))
                            <flux:navlist.item :href="route('payments.create')" :current="request()->routeIs('payments.create')" wire:navigate>{{__('Create Payment')}}</flux:navlist.item>
                        @endif
                    @endcan
                </flux:navlist.group>
            @endcan

            @can('viewAny', \App\Models\StripeWebhookLog::class)
                <flux:navlist.group :heading="__('Stripe Webhooks')" class="grid" expandable remember>
                    @if(\Illuminate\Support\Facades\Route::has('stripe-webhook-logs.index'))
                        <flux:navlist.item :href="route('stripe-webhook-logs.index')" :current="request()->routeIs('stripe-webhook-logs.index')" wire:navigate>{{__('Index')}}</flux:navlist.item>
                    @endif
                </flux:navlist.group>
            @endcan

            <flux:spacer />

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

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

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
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

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

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

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
