<li>
  <a {{$attributes->merge(['href'=>'#','class'=>'flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group'])}}>
   @if(isset($icon))
      {!! $icon !!}
    @endif
    <span class="flex-1 ms-3 whitespace-nowrap">
    {{$slot}}
  </span>
  @if(isset($label))
    <span class="inline-flex items-center justify-center px-2 ms-3 text-sm font-medium text-gray-800 bg-gray-100 rounded-full dark:bg-gray-700 dark:text-gray-300">
    {!! $label !!}
    </span>
  @endif
  </a>
</li>
