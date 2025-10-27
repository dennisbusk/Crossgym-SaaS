@props(['color' => '#000'])
<div style="background-color: {{ $color }}" {{$attributes->merge([
    'class' => 'aspect-square h-5 rounded inline-block align-sub'
])}}></div>
