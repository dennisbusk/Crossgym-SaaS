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

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-t pt-4">
            @if($category === 'strength')
                <flux:input type="number" step="0.01" label="{{ __('Weight (kg)') }}" wire:model="weight" icon="scale" />
                <flux:input type="number" label="{{ __('Reps') }}" wire:model="reps" icon="arrow-path" />
                <flux:input type="number" label="{{ __('Sets') }}" wire:model="sets" icon="hashtag" />
            @elseif($category === 'cardio')
                <flux:input type="number" step="0.01" label="{{ __('Distance (km)') }}" wire:model="distance" icon="map-pin" />
                <flux:input type="number" label="{{ __('Duration (minutes)') }}" wire:model="duration_minutes" icon="clock" />
            @elseif($category === 'biometric')
                <flux:input type="number" step="0.01" label="{{ __('Body Weight (kg)') }}" wire:model="weight" icon="scale" />
                <flux:input label="{{ __('Mood') }}" wire:model="mood" placeholder="{{ __('e.g. Happy, Tired') }}" icon="face-smile" />
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border-t pt-4">
            <flux:input type="number" min="1" max="10" label="{{ __('Intensity / Difficulty (1-10)') }}" wire:model="intensity" icon="bolt" />
            <flux:textarea label="{{ __('Notes') }}" wire:model="notes" placeholder="{{ __('Any comments...') }}" />
        </div>

        <div class="flex justify-end gap-2 pt-4">
            <flux:button href="{{ route('workout-logs.index') }}" variant="ghost" tag="a">{{ __('Cancel') }}</flux:button>
            <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
        </div>
    </form>
</div>
