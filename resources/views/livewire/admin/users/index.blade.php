<div class="space-y-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold">{{ __('Users') }}</h1>
        <div class="flex items-center gap-2">
            <x-flowbite.button class="hover:cursor-pointer" wire:click="export" variant="ghost">
                {{ __('Export') }}
            </x-flowbite.button>
            <x-flowbite.link href="{{ route('users.create') }}" variant="ghost">
                {{ __('New User') }}
            </x-flowbite.link>
        </div>
    </div>

    <x-banners/>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <flux:input label="{{ __('Search') }}" wire:model.live="search" placeholder="{{ __('Search users...') }}" icon="magnifying-glass" />

        <flux:select label="{{ __('Role') }}" wire:model.live="roleFilter" placeholder="{{ __('All Roles') }}">
            <flux:select.option value="">{{ __('All Roles') }}</flux:select.option>
            @foreach($roles as $role)
                <flux:select.option value="{{ $role->id }}">{{ $role->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select label="{{ __('Plan') }}" wire:model.live="planFilter" placeholder="{{ __('All Plans') }}">
            <flux:select.option value="">{{ __('All Plans') }}</flux:select.option>
            @foreach($plans as $plan)
                <flux:select.option value="{{ $plan->stripe_price_id }}">{{ $plan->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select label="{{ __('Status') }}" wire:model.live="statusFilter" placeholder="{{ __('All Statuses') }}">
            <flux:select.option value="">{{ __('All Statuses') }}</flux:select.option>
            @foreach($statuses as $status)
                <flux:select.option value="{{ $status }}">{{ $status }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    <div class="relative overflow-x-auto ">

        <x-flowbite.table>
            <x-flowbite.table.head class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <x-flowbite.table.head.row>
            <x-flowbite.table.head.sortable field="id" :$sortField :$sortDirection>{{ __('ID') }}</x-flowbite.table.head.sortable>
            <x-flowbite.table.head.sortable field="name" :$sortField :$sortDirection>{{ __('Name') }}</x-flowbite.table.head.sortable>
            <x-flowbite.table.head.sortable field="email" :$sortField :$sortDirection>{{ __('Email') }}</x-flowbite.table.head.sortable>
            <x-flowbite.table.head.cell>{{ __('Role') }}</x-flowbite.table.head.cell>
            <x-flowbite.table.head.cell>{{ __('Subscription') }}</x-flowbite.table.head.cell>
            <x-flowbite.table.head.cell>{{ __('Subscription status') }}</x-flowbite.table.head.cell>
            <x-flowbite.table.head.sortable field="last_check_in_at" :$sortField :$sortDirection>{{ __('Last Check-in') }}</x-flowbite.table.head.sortable>
            <x-flowbite.table.head.cell class="text-right">{{ __('Actions') }}</x-flowbite.table.head.cell>
            </x-flowbite.table.head.row>
        </x-flowbite.table.head>

        <x-flowbite.table.body>
            @forelse ($users as $user)
                <x-flowbite.table.body.row>
                    <x-flowbite.table.body.cell>{{ $user->id }}</x-flowbite.table.body.cell>
                    <x-flowbite.table.body.cell>{{ $user->name }}</x-flowbite.table.body.cell>
                    <x-flowbite.table.body.cell>{{ $user->email }}</x-flowbite.table.body.cell>
                    <x-flowbite.table.body.cell>{{ $user->role?->name }}</x-flowbite.table.body.cell>
                    <x-flowbite.table.body.cell>{{ $user->subscription?->plan->name ?? __('No subscription') }}</x-flowbite.table.body.cell>
                    <x-flowbite.table.body.cell>{{ $user->subscription?->status ?? '' }}</x-flowbite.table.body.cell>
                    <x-flowbite.table.body.cell>{{ $user->last_check_in_at?->diffForHumans() ?? __('Never') }}</x-flowbite.table.body.cell>
                    <x-flowbite.table.body.cell class="text-right space-x-2">
                        <flux:button icon="check-circle" wire:click="checkIn({{ $user->id }})" variant="ghost" :title="__('Check-in')" />
                        <flux:button icon="eye" tag="a" href="{{ route('users.show', $user) }}" variant="ghost" />
                        <flux:button icon="lock-closed" tag="a" href="{{ route('users.permissions', $user) }}" variant="ghost" />
                        @if (function_exists('can_impersonate') && function_exists('can_be_impersonated') && can_impersonate() && can_be_impersonated($user))
                            <flux:button icon="lock-open" tag="a" href="{{ route('impersonate', $user->id) }}" variant="ghost" :label="__('Impersonate')" />
                        @endif
                        <flux:button icon="pencil-square" tag="a" href="{{ route('users.edit', $user) }}" variant="ghost" />
                        <flux:button icon="trash" wire:click="delete({{ $user->id }})" variant="ghost" />
                    </x-flowbite.table.body.cell>
                </x-flowbite.table.body.row>
            @empty
                <x-flowbite.table.body.row class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 ">
                    <x-flowbite.table.body.cell class="w-4 p-4" colspan="6">{{ __('No users found.') }}</x-flowbite.table.body.cell>
                </x-flowbite.table.body.row>
            @endforelse
        </x-flowbite.table.body>
        </x-flowbite.table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
</div>
