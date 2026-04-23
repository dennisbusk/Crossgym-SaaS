<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
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
        <flux:header container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <a href="{{ route('dashboard') }}" class="ms-2 me-5 flex items-center space-x-2 rtl:space-x-reverse lg:ms-0" wire:navigate>
                <x-app-logo />
            </a>

            <flux:navbar class="-mb-px max-lg:hidden">
                <flux:navbar.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate width="24" height="24">
                    {{ __('Dashboard') }}
                </flux:navbar.item>
            </flux:navbar>

            <flux:spacer />

            <flux:navbar class="me-1.5 space-x-0.5 rtl:space-x-reverse py-0!">
                <div x-data="{ canInstall: false }"
                     @pwa-installable.window="canInstall = true"
                     @pwa-installed.window="canInstall = false"
                     x-show="canInstall"
                     class="flex items-center">
                    <flux:button icon="arrow-down-tray"
                                 variant="ghost"
                                 class="h-10!"
                                 @click="installPwa()"
                                 aria-label="{{ __('Install App') }}">
                        <span class="max-sm:hidden" id="pwa-install-label">{{ __('Install') }}</span>
                    </flux:button>
                </div>
                <x-theme-toggle />

{{--                <flux:tooltip :content="__('Search')" position="bottom">--}}
{{--                    <flux:navbar.item class="!h-10 [&>div>svg]:size-5" icon="magnifying-glass" href="#" :label="__('Search')" />--}}
{{--                </flux:tooltip>--}}
{{--                <flux:tooltip :content="__('Repository')" position="bottom">--}}
{{--                    <flux:navbar.item--}}
{{--                        class="h-10 max-lg:hidden [&>div>svg]:size-5"--}}
{{--                        icon="folder-git-2"--}}
{{--                        href="https://github.com/laravel/livewire-starter-kit"--}}
{{--                        target="_blank"--}}
{{--                        :label="__('Repository')"--}}
{{--                    />--}}
{{--                </flux:tooltip>--}}
{{--                <flux:tooltip :content="__('Documentation')" position="bottom">--}}
{{--                    <flux:navbar.item--}}
{{--                        class="h-10 max-lg:hidden [&>div>svg]:size-5"--}}
{{--                        icon="book-open-text"--}}
{{--                        href="https://laravel.com/docs/starter-kits#livewire"--}}
{{--                        target="_blank"--}}
{{--                        label="Documentation"--}}
{{--                    />--}}
{{--                </flux:tooltip>--}}
            </flux:navbar>
@auth
            <!-- Desktop User Menu -->
            <flux:dropdown position="top" align="end">
                <flux:profile
                    class="cursor-pointer"
                    :initials="auth()->user()->initials()"
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
                        <flux:menu.item :href="route('profile.settings')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
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
     @endauth
        </flux:header>

        <!-- Mobile Menu -->
        <flux:sidebar stashable sticky class="lg:hidden border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('dashboard') }}" class="ms-1 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                <x-app-logo />
            </a>

            <div class="mt-3 ms-1">
                <button type="button" x-data="{ dark: document.documentElement.classList.contains('dark') }"
                        @click="dark = !dark; document.documentElement.classList.toggle('dark', dark); localStorage.setItem('theme', dark ? 'dark' : 'light')"
                        class="inline-flex items-center justify-center h-10 w-10 rounded-md hover:bg-zinc-200/60 dark:hover:bg-zinc-800/60 focus:outline-none"
                        aria-label="{{ __('Toggle theme') }}">
                    <!-- Sun icon (light mode) -->
                    <svg x-show="!dark" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.364 6.364-1.414-1.414M7.05 7.05 5.636 5.636m12.728 0-1.414 1.414M7.05 16.95l-1.414 1.414M12 8.25a3.75 3.75 0 1 0 0 7.5 3.75 3.75 0 0 0 0-7.5z" />
                    </svg>
                    <!-- Moon icon (dark mode) -->
                    <svg x-show="dark" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
                        <path d="M21.752 15.002A9.718 9.718 0 0 1 12.004 22C6.478 22 2 17.523 2 11.996 2 7.37 4.942 3.5 9.12 2.16a.75.75 0 0 1 .967.967 8.25 8.25 0 0 0 10.506 10.506.75.75 0 0 1 .159 1.37z"/>
                    </svg>
                    <span class="sr-only">{{ __('Toggle theme') }}</span>
                </button>
            </div>

            <flux:navlist variant="outline">
                <div x-data="{ canInstall: false }"
                     @pwa-installable.window="canInstall = true"
                     @pwa-installed.window="canInstall = false"
                     x-show="canInstall"
                     class="px-2 mb-4">
                    <flux:button icon="arrow-down-tray"
                                 variant="outline"
                                 class="w-full justify-start"
                                 @click="installPwa()"
                                 aria-label="{{ __('Install App') }}">
                        {{ __('Install App') }}
                    </flux:button>
                </div>
                <flux:navlist.group :heading="__('Platform')">
                    <flux:navlist.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate width="24" height="24">
                      {{ __('Dashboard') }}
                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>

            <flux:spacer />

{{--            <flux:navlist variant="outline">--}}
{{--                <flux:navlist.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">--}}
{{--                    {{ __('Repository') }}--}}
{{--                </flux:navlist.item>--}}

{{--                <flux:navlist.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">--}}
{{--                    {{ __('Documentation') }}--}}
{{--                </flux:navlist.item>--}}
{{--            </flux:navlist>--}}
        </flux:sidebar>

        {{ $slot }}

        @fluxScripts
        <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
        @stack('scripts')

    </body>
</html>
