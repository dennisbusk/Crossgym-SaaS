@pure

@props([
    'expandable' => false,
    'expanded' => true,
    'heading' => null,
    'remember' => false,
])
@php($expanded = $remember ? false : $expanded)

<?php if ($expandable && $heading): ?>
    <ui-disclosure  {{ $attributes->class('group/disclosure') }} @if ($expanded === true) open @endif data-flux-navlist-group>
        <button @if($remember) id="{{Str::slug($heading)}}" @endif type="button" class="w-full h-10 lg:h-8 flex items-center group/disclosure-button mb-[2px] rounded-lg hover:bg-zinc-800/5 dark:hover:bg-white/[7%] text-zinc-500 hover:text-zinc-800 dark:text-white/80 dark:hover:text-white" >
            <div class="ps-3 pe-4">
                <flux:icon.chevron-down class="size-3! hidden group-data-open/disclosure-button:block" />
                <flux:icon.chevron-right class="size-3! block group-data-open/disclosure-button:hidden rtl:rotate-180" />
            </div>

            <span class="text-sm font-medium leading-none">{{ $heading }}</span>
        </button>

        <div class="relative hidden data-open:block space-y-[2px] ps-7" @if ($expanded === true) data-open @endif>
            <div class="absolute inset-y-[3px] w-px bg-zinc-200 dark:bg-white/30 start-0 ms-4"></div>

            {{ $slot }}
        </div>
    </ui-disclosure>
<?php elseif ($heading): ?>
    <div {{ $attributes->class('block space-y-[2px]') }}>
        <div class="px-3 py-2">
            <div class="text-sm text-zinc-400 font-medium leading-none">{{ $heading }}</div>
        </div>

        <div>
            {{ $slot }}
        </div>
    </div>
<?php else: ?>
    <div {{ $attributes->class('block space-y-[2px]') }}>
        {{ $slot }}
    </div>
<?php endif; ?>
@if($remember)
<script>
    document.addEventListener('alpine:init', () => {
        const el = document.getElementById('{{ Str::slug($heading) }}');
        if (!el || el.dataset.listenerAdded) return; // prevent multiple attachments
        el.dataset.listenerAdded = 'true';
        if(localStorage.getItem('navbar.{{ Str::slug($heading) }}') !== undefined && localStorage.getItem('navbar.{{ Str::slug($heading) }}') != null){
            let stored = localStorage.getItem('navbar.{{ Str::slug($heading) }}');
            if(stored !== el.getAttribute('aria-expanded')){
                el.click();
            }
        }
        el.addEventListener('click', event => {
            let status = event.target.getAttribute('aria-expanded');
            if(status !== undefined && status != null){
                localStorage.setItem('navbar.{{ Str::slug($heading) }}', status);
            }
        });
    });
</script>
@endif
