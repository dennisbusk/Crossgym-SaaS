<div class="mx-auto max-w-4xl p-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold">{{ __('Roles') }}</h1>
        <flux:button tag="a" href="{{ route('roles.create') }}" variant="primary">{{ __('New Role') }}</flux:button>
    </div>

    @if (session('status'))
        <div class="mb-4 rounded bg-green-50 text-green-800 px-3 py-2">{{ __(session('status')) }}</div>
    @endif

    <div class="mb-4">
        <flux:input placeholder="{{ __('Search...') }}" wire:model.live="search" />
    </div>

    <flux:table>
        <x-slot name="head">
            <flux:th>{{ __('Name') }}</flux:th>
            <flux:th>{{ __('Users') }}</flux:th>
            <flux:th class="text-right">{{ __('Actions') }}</flux:th>
        </x-slot>

        @forelse ($roles as $role)
            <flux:tr>
                <flux:td>{{ $role->name }}</flux:td>
                <flux:td>{{ $role->users()->count() }}</flux:td>
                <flux:td class="text-right space-x-2">
                    <flux:button icon="eye" tag="a" href="{{ route('roles.show', $role) }}" variant="ghost" />
                    <flux:button icon="edit" tag="a" href="{{ route('roles.edit', $role) }}" variant="ghost" />
                    <flux:button icon="trash" wire:click="delete({{ $role->id }})" variant="ghost" />
                </flux:td>
            </flux:tr>
        @empty
            <flux:tr>
                <flux:td colspan="3" class="text-center">{{ __('No roles found.') }}</flux:td>
            </flux:tr>
        @endforelse
    </flux:table>

    <div class="mt-4">{{ $roles->links() }}</div>
</div>
