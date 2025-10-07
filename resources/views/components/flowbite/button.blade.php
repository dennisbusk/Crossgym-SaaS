@props([
    'variant' => 'primary',
])
<x-dynamic-component :component="'flowbite.button.' . $variant" {{ $attributes }}>
  {{ $slot }}
</x-dynamic-component>
