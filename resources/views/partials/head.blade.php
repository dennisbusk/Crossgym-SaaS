@php
    $tenant = tenant();
    $iconUrl = $tenant && $tenant->icon_path ? asset('storage/' . $tenant->icon_path) : asset('favicon.svg');
    $themeColor = $tenant?->theme_color ?? '#000000';
@endphp
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="{{ $themeColor }}">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">

<title>{{ $title ?? $tenant?->app_name ?? $tenant?->name ?? config('app.name') }}</title>

<link rel="icon" href="{{ $iconUrl }}" sizes="any">
<link rel="apple-touch-icon" href="{{ $iconUrl }}">
<link rel="manifest" href="/manifest.json">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js');
        });
    }
</script>

<style>
    :root {
        --brand-color: {{ $themeColor }};
    }
</style>

@unless(app()->environment('testing'))
@vite(['resources/css/app.css', 'resources/js/app.js'])
@endunless
@stack('styles')
@fluxAppearance
