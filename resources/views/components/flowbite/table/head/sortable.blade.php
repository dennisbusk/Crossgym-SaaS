@props(['field', 'sortField', 'sortDirection'])

<th {{ $attributes->merge(['scope' => 'col', 'class' => 'px-6 py-3 cursor-pointer group']) }} wire:click="sortBy('{{ $field }}')">
    <div class="flex items-center gap-1">
        {{ $slot }}
        <span class="flex-none">
            @if ($sortField === $field)
                @if ($sortDirection === 'asc')
                    <flux:icon icon="chevron-up" variant="mini" class="w-3 h-3" />
                @else
                    <flux:icon icon="chevron-down" variant="mini" class="w-3 h-3" />
                @endif
            @else
                <flux:icon icon="chevron-up-down" variant="mini" class="w-3 h-3 opacity-0 group-hover:opacity-50" />
            @endif
        </span>
    </div>
</th>
