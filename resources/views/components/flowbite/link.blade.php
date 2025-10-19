@props([
    'variant' => 'primary',
])
<x-dynamic-component :component="'flowbite.link.' . $variant" {{ $attributes }}>
  {{ $slot }}
</x-dynamic-component>
