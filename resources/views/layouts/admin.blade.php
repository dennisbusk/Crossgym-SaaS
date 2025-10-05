<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Laravel Admin') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-full bg-neutral-50 dark:bg-neutral-950 text-neutral-900 dark:text-neutral-100">
<div class="min-h-screen grid" style="grid-template-columns: 280px 1fr;" x-data="{ mobileOpen: false }">
    <aside class="hidden md:block h-screen sticky top-0">
        <x-admin.sidebar />
    </aside>

    <!-- Mobile overlay sidebar -->
    <div class="md:hidden" x-data="{ open: false }" x-cloak>
        <div class="fixed inset-0 z-40" x-show="open" @keydown.escape.window="open = false">
            <div class="absolute inset-0 bg-black/50" @click="open = false"></div>
            <div class="absolute inset-y-0 left-0 w-72 bg-white dark:bg-neutral-900 shadow-xl" x-show="open" x-transition>
                <x-admin.sidebar />
            </div>
        </div>
        <button @click="open = true" class="fixed bottom-4 right-4 z-30 md:hidden rounded-full shadow-lg bg-neutral-900 text-white px-4 py-2">
            Menu
        </button>
    </div>

    <main class="min-h-screen flex flex-col">
        <header class="sticky top-0 z-10 bg-white/80 dark:bg-neutral-900/80 backdrop-blur border-b border-neutral-200/80 dark:border-neutral-800">
            <div class="flex items-center justify-between px-4 py-3">
                <div class="flex items-center gap-2">
                    <button class="md:hidden p-2 rounded hover:bg-neutral-100 dark:hover:bg-neutral-800" @click="mobileOpen = !mobileOpen" x-data="{ }" @click.prevent="$dispatch('toggle-mobile-sidebar')">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h16.5m-16.5 6h16.5m-16.5 6h16.5" />
                        </svg>
                    </button>
                    <h1 class="text-lg font-semibold">{{ $header ?? ($title ?? 'Admin') }}</h1>
                </div>
                <div class="flex items-center gap-3">
                    {{ $topbar ?? '' }}
                </div>
            </div>
        </header>

        <div class="p-4">
            @yield('content')
            {{ $slot ?? '' }}
        </div>
    </main>
</div>
</body>
</html>
