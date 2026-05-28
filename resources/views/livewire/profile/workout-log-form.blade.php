<div class="space-y-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold">{{ $isEditing ? __('Edit Log') : __('New Log Entry') }}</h1>
        <flux:button tag="a" href="{{ route('workout-logs.index') }}" variant="ghost" icon="arrow-left">{{ __('Back') }}</flux:button>
    </div>

    <x-banners/>

    <form wire:submit="save" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <flux:input type="date" label="{{ __('Date') }}" wire:model="date" required />

            <div class="space-y-2">
                <flux:select label="{{ __('Exercise') }}" wire:model.live="exercise_id">
                    <flux:select.option value="">{{ __('--- Create New ---') }}</flux:select.option>
                    @foreach($exercises as $exercise)
                        <flux:select.option value="{{ $exercise->id }}">{{ $exercise->name }} ({{ __($exercise->category) }})</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </div>

        @if(!$exercise_id)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border p-4 rounded-lg bg-zinc-50 dark:bg-zinc-800/50">
                <flux:input label="{{ __('New Exercise Name') }}" wire:model="new_exercise_name" placeholder="{{ __('e.g. Bench Press') }}" />
                <flux:select label="{{ __('Category') }}" wire:model.live="category">
                    <flux:select.option value="strength">{{ __('Strength') }}</flux:select.option>
                    <flux:select.option value="cardio">{{ __('Cardio') }}</flux:select.option>
                    <flux:select.option value="biometric">{{ __('Biometric') }}</flux:select.option>
                </flux:select>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 border-t pt-4" x-data="{
            weight: @entangle('weight').live,
            reps: @entangle('reps').live,
            sets: @entangle('sets').live,
            distance: @entangle('distance').live,
            duration: @entangle('duration_minutes').live
        }">
            @if($category === 'strength')
                <div class="space-y-2">
                    <flux:label>{{ __('Weight (kg)') }}</flux:label>
                    <div class="flex items-center gap-2">
                        <flux:button icon="minus" variant="ghost" @click="weight = Math.max(0, (parseFloat(weight) || 0) - 2.5).toFixed(1)" />
                        <flux:input type="number" step="0.5" x-model="weight" class="text-center text-xl font-bold" />
                        <flux:button icon="plus" variant="ghost" @click="weight = ((parseFloat(weight) || 0) + 2.5).toFixed(1)" />
                    </div>
                </div>

                <div class="space-y-2">
                    <flux:label>{{ __('Reps') }}</flux:label>
                    <div class="flex items-center gap-2">
                        <flux:button icon="minus" variant="ghost" @click="reps = Math.max(0, (parseInt(reps) || 0) - 1)" />
                        <flux:input type="number" x-model="reps" class="text-center text-xl font-bold" />
                        <flux:button icon="plus" variant="ghost" @click="reps = (parseInt(reps) || 0) + 1" />
                    </div>
                </div>

                <div class="space-y-2">
                    <flux:label>{{ __('Sets') }}</flux:label>
                    <div class="flex items-center gap-2">
                        <flux:button icon="minus" variant="ghost" @click="sets = Math.max(0, (parseInt(sets) || 0) - 1)" />
                        <flux:input type="number" x-model="sets" class="text-center text-xl font-bold" />
                        <flux:button icon="plus" variant="ghost" @click="sets = (parseInt(sets) || 0) + 1" />
                    </div>
                </div>
            @elseif($category === 'cardio')
                <div class="space-y-2">
                    <flux:label>{{ __('Distance (km)') }}</flux:label>
                    <div class="flex items-center gap-2">
                        <flux:button icon="minus" variant="ghost" @click="distance = Math.max(0, (parseFloat(distance) || 0) - 0.5).toFixed(1)" />
                        <flux:input type="number" step="0.1" x-model="distance" class="text-center text-xl font-bold" />
                        <flux:button icon="plus" variant="ghost" @click="distance = ((parseFloat(distance) || 0) + 0.5).toFixed(1)" />
                    </div>
                </div>

                <div class="space-y-2">
                    <flux:label>{{ __('Duration (minutes)') }}</flux:label>
                    <div class="flex items-center gap-2">
                        <flux:button icon="minus" variant="ghost" @click="duration = Math.max(0, (parseInt(duration) || 0) - 1)" />
                        <flux:input type="number" x-model="duration" class="text-center text-xl font-bold" />
                        <flux:button icon="plus" variant="ghost" @click="duration = (parseInt(duration) || 0) + 1" />
                    </div>
                </div>
            @elseif($category === 'biometric')
                <div class="space-y-2 col-span-1">
                    <flux:label>{{ __('Body Weight (kg)') }}</flux:label>
                    <div class="flex items-center gap-2">
                        <flux:button icon="minus" variant="ghost" @click="weight = Math.max(0, (parseFloat(weight) || 0) - 0.1).toFixed(1)" />
                        <flux:input type="number" step="0.1" x-model="weight" class="text-center text-xl font-bold" />
                        <flux:button icon="plus" variant="ghost" @click="weight = ((parseFloat(weight) || 0) + 0.1).toFixed(1)" />
                    </div>
                </div>
                <flux:input label="{{ __('Mood') }}" wire:model="mood" placeholder="{{ __('e.g. Happy, Tired') }}" icon="face-smile" />
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border-t pt-4" x-data="{
            intensity: @entangle('intensity').live,
            restTime: 0,
            timer: null,
            startRest() {
                this.restTime = 90;
                if (this.timer) clearInterval(this.timer);
                this.timer = setInterval(() => {
                    if (this.restTime > 0) this.restTime--;
                    else clearInterval(this.timer);
                }, 1000);
            }
        }">
            <div class="space-y-4">
                <flux:input type="number" min="1" max="10" label="{{ __('Intensity / Difficulty (1-10)') }}" wire:model="intensity" icon="bolt" />

                <div class="p-4 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-blue-600 dark:text-blue-400">{{ __('Rest Timer') }}</div>
                        <div class="text-2xl font-bold tabular-nums" x-text="Math.floor(restTime / 60) + ':' + (restTime % 60).toString().padStart(2, '0')">0:00</div>
                    </div>
                    <flux:button icon="play" variant="ghost" @click="startRest()">{{ __('Start Rest') }}</flux:button>
                </div>
            </div>

            <flux:textarea label="{{ __('Notes') }}" wire:model="notes" placeholder="{{ __('Any comments...') }}" />
        </div>

        <div class="flex justify-end gap-2 pt-4">
            <flux:button href="{{ route('workout-logs.index') }}" variant="ghost" tag="a">{{ __('Cancel') }}</flux:button>
            <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
        </div>
    </form>
</div>
