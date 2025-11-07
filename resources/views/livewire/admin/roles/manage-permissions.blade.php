<div class="space-y-6">
  <div class="flex items-center justify-between">
    <h2 class="text-xl font-bold">{{ __('Manage Permissions for Role:') }} {{ $role->name }}</h2>
    <div class="flex items-center gap-2" x-data>
      <flux:button wire:click="syncUsersForRole" variant="primary">
        {{ __('Sync all users with this role') }}
      </flux:button>
    </div>
  </div>
  
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
                    // Keep Toggle All in sync when single fields change
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
