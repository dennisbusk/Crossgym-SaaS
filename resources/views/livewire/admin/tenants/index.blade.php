<div class="space-y-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold">{{ __('Tenants') }}</h1>
        <flux:button tag="a" href="{{ route('tenants.create') }}" variant="primary">
            {{ __('New Tenant') }}
        </flux:button>
    </div>

    @if (session('status'))
        <div class="rounded-md bg-green-50 p-3 text-green-700">{{ __(session('status')) }}</div>
    @endif

    <flux:table>
        <x-slot name="head">
            <flux:th>{{ __('ID') }}</flux:th>
            <flux:th>{{ __('Name') }}</flux:th>
            <flux:th>{{ __('Domain') }}</flux:th>
            <flux:th class="text-right">{{ __('Actions') }}</flux:th>
        </x-slot>

        @forelse ($tenants as $tenant)
            <flux:tr>
                <flux:td>{{ $tenant->id }}</flux:td>
                <flux:td>{{ $tenant->name }}</flux:td>
                <flux:td>{{ $tenant->domain }}</flux:td>
                <flux:td class="text-right space-x-2">
                    <flux:button icon="eye" tag="a" href="{{ route('tenants.show', $tenant) }}" variant="ghost" />
                    <flux:button icon="edit" tag="a" href="{{ route('tenants.edit', $tenant) }}" variant="ghost" />
                    <flux:button icon="trash" wire:click="delete({{ $tenant->id }})" variant="ghost" />
                </flux:td>
            </flux:tr>
        @empty
            <flux:tr>
                <flux:td colspan="4">{{ __('No tenants found.') }}</flux:td>
            </flux:tr>
        @endforelse
    </flux:table>
</div>
