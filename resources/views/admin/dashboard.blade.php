@php($title = 'Dashboard')

@extends('layouts.admin')

@section('content')
    <div class="space-y-4">
        <div class="rounded border border-neutral-200/80 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-4">
            <h2 class="text-lg font-semibold mb-2">Welcome to the Admin Dashboard</h2>
            <p class="text-sm text-neutral-600 dark:text-neutral-300">Use the sidebar to navigate through admin sections.</p>
        </div>
    </div>
@endsection

{{-- Support for slot-based usage as well --}}
{{-- If using Volt or layouts with slots, you can instead include content directly without sections. --}}
