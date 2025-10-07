<div class="space-y-6 max-w-3xl">
    <h1 class="text-2xl font-semibold">{{ __('Class') }}: {{ $gymClass->getTranslation('name', app()->getLocale()) }}</h1>

    <div class="rounded-md border p-4 space-y-2">
        <div><strong>{{ __('Type') }}:</strong> {{ $gymClass->classType?->getTranslation('name', app()->getLocale()) }}</div>
        <div><strong>{{ __('Trainer') }}:</strong> {{ $gymClass->trainer?->name }}</div>
        <div><strong>{{ __('Start') }}:</strong> {{ optional($gymClass->class_start)->format('Y-m-d H:i') }}</div>
        <div><strong>{{ __('End') }}:</strong> {{ optional($gymClass->class_end)->format('Y-m-d H:i') }}</div>
        <div><strong>{{ __('Max participants') }}:</strong> {{ $gymClass->max_participants }}</div>
        <div><strong>{{ __('Description') }}:</strong> {{ $gymClass->getTranslation('description', app()->getLocale()) }}</div>
    </div>

    <div class="rounded-md border p-4 space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold">{{ __('Participants') }}</h2>
            <span class="text-sm text-gray-600">{{ $gymClass->participants->count() }} / {{ $gymClass->max_participants ?? '∞' }}</span>
        </div>

        @if ($gymClass->participants->isEmpty())
            <div class="text-gray-500">{{ __('No participants yet.') }}</div>
        @else
            <ul class="divide-y divide-gray-200">
                @foreach ($gymClass->participants as $participant)
                    <li class="py-2 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center text-sm font-medium">
                                {{ $participant->initials() }}
                            </div>
                            <div>
                                <div class="font-medium">{{ $participant->name }}</div>
                                <div class="text-sm text-gray-500">{{ $participant->email }}</div>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <div class="flex gap-2 justify-end">
        <a class="inline-flex items-center rounded-md bg-yellow-600 px-3 py-2 hover:bg-yellow-800" href="{{ route('classes.index') }}">{{ __('Back') }}</a>
        <a class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-white hover:bg-blue-700" href="{{ route('classes.edit', $gymClass) }}">{{ __('Edit') }}</a>
    </div>
</div>
