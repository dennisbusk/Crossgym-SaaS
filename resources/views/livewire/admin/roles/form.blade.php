<div class="space-y-6 max-w-3xl">
        <h1 class="text-2xl font-semibold">{{ $role && $role->exists ? __('Edit Role') : __('Create Role') }}</h1>

    @if (session('status'))
        <div class="mb-4 rounded bg-green-50 text-green-800 px-3 py-2">{{ session('status') }}</div>
    @endif

    <form wire:submit.prevent="save" class="space-y-4">
        <div>
            <label class="block text-sm font-medium mb-1">{{ __('Name') }}</label>
            <input type="text" wire:model.live="name" class="w-full rounded border px-3 py-2" />
            @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

{{--        @include('partials.form-errors', [--}}
{{--            'errors' => $errors,--}}
{{--        ])--}}

        <div class="flex gap-2 justify-end">
            <button type="submit" class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-white hover:bg-blue-700">
                {{ $role?->exists ? __('Update') : __('Create') }}
            </button>
            <a href="{{ route('roles.index') }}" class="inline-flex items-center rounded-md bg-yellow-600 px-3 py-2 hover:bg-yellow-800">{{ __('Cancel') }}</a>
        </div>
    </form>
    @if($role->exists)
    <div class="space-y-6">
        <h2 class="text-xl font-bold">{{ __('Manage Permissions for Role:') }} {{ $role->name }}</h2>

        @foreach ($permissionsGrouped as $model => $permissions)
            <div
                class="border rounded-2xl p-4 bg-gray-800 shadow"
                wire:key="role-perms-group-{{ $model }}"
                x-data="{
                allChecked: {{ collect($permissions)->every(fn($p) => $p['granted']) ? 'true' : 'false' }},
                toggleAll() {
                    this.allChecked = !this.allChecked;
                    const checkboxes = $el.querySelectorAll('.perm-checkbox');
                    checkboxes.forEach(cb => {
                        const id = cb.dataset.id;
                        cb.checked = this.allChecked;
                            $wire.togglePermission(parseInt(id),cb.checked);
                    });
                },
                init() {
                    const checkboxes = $el.querySelectorAll('.perm-checkbox');
                    // Hold Toggle All i sync, når enkeltfelter ændres
                    checkboxes.forEach(cb => {
                        cb.addEventListener('change', () => {
                            this.allChecked = Array.from(checkboxes).every(c => c.checked);
                        });
                    });
                }
            }"
            >
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-lg font-semibold">{{ __($model) }}</h3>
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox"
                               x-model="allChecked"
                               @click="toggleAll"
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span>{{ __('Toggle all') }}</span>
                    </label>
                </div>

                <div class="grid grid-cols-1 gap-2">
                    @foreach ($permissions as $perm)
                        <label class="flex items-center gap-2 cursor-pointer"
                               wire:key="role-perm-{{ $model }}-{{ $perm['id'] }}">
                            <input type="checkbox"
                                   class="perm-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                   data-id="{{ $perm['id'] }}"
                                   wire:click="togglePermission({{ $perm['id'] }})"
                                @checked($perm['granted'])>
                            <span>
                            @php($parts = explode('.', $perm['ability']))
                                @if(count($parts) === 2)
                                    {{ __($parts[1]) }} {{ __($parts[0]) }}
                                @else
                                    {{ __($perm['ability']) }}
                                @endif
                        </span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
    @endif

</div>
