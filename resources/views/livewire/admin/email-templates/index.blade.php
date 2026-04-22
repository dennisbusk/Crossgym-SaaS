<div class="space-y-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold">{{ __('Email Skabeloner') }}</h1>
    </div>

    @if (session('status'))
        <x-banners type="success">{{ session('status') }}</x-banners>
    @endif

    <div class="flex justify-between items-center mb-4">
        <flux:input placeholder="{{ __('Search templates...') }}" wire:model.live="search" icon="magnifying-glass" />
    </div>

    <div class="relative overflow-x-auto">
        <x-flowbite.table>
            <x-flowbite.table.head>
                <x-flowbite.table.head.row>
                    <x-flowbite.table.head.cell>{{ __('Navn') }}</x-flowbite.table.head.cell>
                    <x-flowbite.table.head.cell>{{ __('Emne') }}</x-flowbite.table.head.cell>
                    <x-flowbite.table.head.cell>{{ __('Status') }}</x-flowbite.table.head.cell>
                    <x-flowbite.table.head.cell class="text-right">{{ __('Handlinger') }}</x-flowbite.table.head.cell>
                </x-flowbite.table.head.row>
            </x-flowbite.table.head>

            <x-flowbite.table.body>
                @forelse ($templates as $template)
                    <x-flowbite.table.body.row>
                        <x-flowbite.table.body.cell>{{ $template->name }}</x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell>{{ $template->subject }}</x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell>
                            <flux:switch wire:click="toggleActive({{ $template->id }})" :checked="$template->is_active" />
                        </x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell class="text-right space-x-2">
                            <flux:button icon="pencil-square" tag="a" href="{{ route('admin.email-templates.edit', $template) }}" variant="ghost" />
                        </x-flowbite.table.body.cell>
                    </x-flowbite.table.body.row>
                @empty
                    <x-flowbite.table.body.row>
                        <x-flowbite.table.body.cell colspan="4" class="text-center py-4">{{ __('Ingen skabeloner fundet.') }}</x-flowbite.table.body.cell>
                    </x-flowbite.table.body.row>
                @endforelse
            </x-flowbite.table.body>
        </x-flowbite.table>
    </div>

    <div class="mt-4">
        {{ $templates->links() }}
    </div>
</div>
