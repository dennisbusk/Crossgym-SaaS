<div class="space-y-6 max-w-2xl">
    <h1 class="text-2xl font-semibold">{{ __('Class Type') }}: {{ $classType->getTranslation('name', app()->getLocale()) }}</h1>

    <div class="rounded-md border p-4 space-y-2">
        <div><strong>{{ __('Slug') }}:</strong> {{ $classType->slug }}</div>
        <div><strong>{{ __('Color') }}:</strong> <x-color :color="$classType->color"></x-color></div>
        <div><strong>{{ __('Image') }}:</strong> {{ $classType->image }}</div>
        <div><strong>{{ __('Description') }}:</strong> {{ $classType->getTranslation('description', app()->getLocale()) }}</div>
    </div>

    <div class="flex gap-2 justify-end">
        <a class="inline-flex items-center rounded-md bg-yellow-600 px-3 py-2 hover:bg-yellow-800" href="{{ route('class-types.index') }}">{{ __('Back') }}</a>
        <a class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-white hover:bg-blue-700" href="{{ route('class-types.edit', $classType) }}">{{ __('Edit') }}</a>
    </div>
</div>
