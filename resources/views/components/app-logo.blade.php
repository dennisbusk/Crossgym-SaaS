<div class="flex aspect-square size-8 items-center justify-center rounded-md bg-transparent text-accent-foreground">
    <x-app-logo-icon class="size-7 bg-inherit fill:gray-900 text-gray-900 dark:text-white" />
</div>
<div class="ms-1 grid flex-1 text-start text-sm">
    <span class="mb-0.5 truncate leading-tight font-semibold">{{ tenant()->name ?? config('app.name') }}</span>
</div>
