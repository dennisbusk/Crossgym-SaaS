<div class="space-y-6">
  <h2 class="text-xl font-bold">{{ __('Manage Permissions for') }} {{ $user->name }}</h2>

  <p class="text-gray-500">
    <span class="font-semibold">{{ __('Role:') }}</span>
    {{ $user->role?->name ?? __('None') }}
  </p>

  @foreach ($permissionsGrouped as $model => $permissions)
    <div
        class="border rounded-2xl p-4 bg-gray-800 shadow"
        wire:key="role-perms-group-{{ $model }}"
        x-data="{
                allChecked: {{ collect($permissions)->every(fn($p) => $p['effective']) ? 'true' : 'false' }},
                toggleAll() {
                    this.allChecked = !this.allChecked;
                    const checkboxes = $el.querySelectorAll('.perm-checkbox');
                    checkboxes.forEach(cb => {
                        const id = cb.dataset.id;
                        cb.checked = this.allChecked;
                            $wire.togglePermission(parseInt(id),this.allChecked);
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
                @checked($perm['effective'])>
            <div class="flex flex-col">
              <span class="font-medium">
                @php($parts = explode('.', $perm['ability']))
                @if(count($parts) === 2)
                  {{ __($parts[1]) }} {{ __($parts[0]) }}
                @else
                  {{ __($perm['ability']) }}
                @endif
              </span>
              @if($perm['description'])
                <span class="text-xs text-gray-400">{{ $perm['description'] }}</span>
              @endif
            </div>
          </label>
        @endforeach
      </div>
    </div>
  @endforeach
</div>
