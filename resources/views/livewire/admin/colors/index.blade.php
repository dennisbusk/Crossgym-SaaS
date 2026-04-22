<div class="space-y-6">
    <div class="flex justify-between items-center mb-4">
        <div class="flex items-center gap-4">
            <h1 class="text-2xl font-semibold">{{ __('Colors') }}</h1>
            <flux:input wire:model.live="search" placeholder="{{ __('Search colors...') }}" icon="magnifying-glass" />
        </div>
        <flux:button wire:click="create" variant="primary">{{ __('New Color') }}</flux:button>
    </div>

    <x-banners/>

    <div class="relative overflow-x-auto">
        <x-flowbite.table>
            <x-flowbite.table.head class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <x-flowbite.table.head.row>
                    <x-flowbite.table.head.sortable field="name" :$sortField :$sortDirection>{{ __('Name') }}</x-flowbite.table.head.sortable>
                    <x-flowbite.table.head.sortable field="color" :$sortField :$sortDirection>{{ __('Color') }}</x-flowbite.table.head.sortable>
                    <x-flowbite.table.head.sortable field="classes_count" :$sortField :$sortDirection>{{ __('Classes Count') }}</x-flowbite.table.head.sortable>
                    <x-flowbite.table.head.cell class="text-right">{{ __('Actions') }}</x-flowbite.table.head.cell>
                </x-flowbite.table.head.row>
            </x-flowbite.table.head>

            <x-flowbite.table.body>
                @forelse ($colors as $color)
                    <x-flowbite.table.body.row wire:key="color-{{ $color->id }}">
                        <x-flowbite.table.body.cell>{{ $color->name }}</x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell>
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded border border-gray-200" style="background-color: {{ $color->color }}"></div>
                                <span>{{ $color->color }}</span>
                            </div>
                        </x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell>{{ $color->classes_count }}</x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell class="text-right space-x-2">
                            <flux:button icon="arrow-path" wire:click="openMoveModal({{ $color->id }})" variant="ghost" :title="__('Move classes to another color')" />
                            <flux:button icon="pencil-square" wire:click="edit({{ $color->id }})" variant="ghost" :title="__('Edit')" />
                            <flux:button icon="trash" wire:click="confirmDelete({{ $color->id }})" variant="ghost" :title="__('Delete')" />
                        </x-flowbite.table.body.cell>
                    </x-flowbite.table.body.row>
                @empty
                    <x-flowbite.table.body.row>
                        <x-flowbite.table.body.cell colspan="4" class="text-center py-4">
                            {{ __('No colors found.') }}
                        </x-flowbite.table.body.cell>
                    </x-flowbite.table.body.row>
                @endforelse
            </x-flowbite.table.body>
        </x-flowbite.table>
    </div>

    <div class="mt-4">
        {{ $colors->links() }}
    </div>

    @if($showMoveModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
                <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">{{ __('Move Classes') }}</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    {{ __('Choose a new color for all classes currently using the selected color.') }}
                </p>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Select Target Color') }}</label>
                        <select wire:model="targetColorId" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">{{ __('Select a color...') }}</option>
                            @foreach($allColors as $c)
                                @if($c->id !== $selectedColorId)
                                    <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->color }})</option>
                                @endif
                            @endforeach
                        </select>
                        @error('targetColorId')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <flux:button wire:click="$set('showMoveModal', false)" variant="ghost">{{ __('Cancel') }}</flux:button>
                    <flux:button wire:click="moveClasses" variant="primary">{{ __('Move Classes') }}</flux:button>
                </div>
            </div>
        </div>
    @endif

    @if($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
                <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">{{ __('Create New Color') }}</h2>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Name') }}</label>
                        <flux:input wire:model="newName" />
                        @error('newName') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Color') }}</label>
                        <input type="color" wire:model="newColorHex" class="w-full h-10 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 p-1" />
                        @error('newColorHex') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <flux:button wire:click="$set('showCreateModal', false)" variant="ghost">{{ __('Cancel') }}</flux:button>
                    <flux:button wire:click="store" variant="primary">{{ __('Create Color') }}</flux:button>
                </div>
            </div>
        </div>
    @endif

    @if($showEditModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
                <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">{{ __('Edit Color') }}</h2>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Name') }}</label>
                        <flux:input wire:model="editingName" />
                        @error('editingName') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Color') }}</label>
                        <input type="color" wire:model="editingColorHex" class="w-full h-10 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 p-1" />
                        @error('editingColorHex') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <flux:button wire:click="$set('showEditModal', false)" variant="ghost">{{ __('Cancel') }}</flux:button>
                    <flux:button wire:click="update" variant="primary">{{ __('Save Changes') }}</flux:button>
                </div>
            </div>
        </div>
    @endif

    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
                <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">{{ __('Delete Color') }}</h2>

                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    {{ __('Are you sure you want to delete this color?') }}
                </p>

                @if($classesCountForDelete > 0)
                    <div class="p-4 mb-4 text-sm text-yellow-800 rounded-lg bg-yellow-50 dark:bg-gray-800 dark:text-yellow-300 border border-yellow-300" role="alert">
                        <span class="font-medium">{{ __('Warning!') }}</span> {{ __('There are :count classes currently using this color.', ['count' => $classesCountForDelete]) }}
                        <br>
                        {{ __('Choose a replacement color to move these classes to, or delete anyway to leave them without a color.') }}
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Replacement Color (Optional)') }}</label>
                        <select wire:model="replacementColorId" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">{{ __('No replacement (leave empty)') }}</option>
                            @foreach($allColors as $c)
                                @if($c->id !== $deletingColorId)
                                    <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->color }})</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="mt-6 flex justify-end gap-3">
                    <flux:button wire:click="$set('showDeleteModal', false)" variant="ghost">{{ __('Cancel') }}</flux:button>
                    <flux:button wire:click="delete" variant="danger">
                        {{ $replacementColorId ? __('Move & Delete') : __('Delete Anyway') }}
                    </flux:button>
                </div>
            </div>
        </div>
    @endif
</div>
