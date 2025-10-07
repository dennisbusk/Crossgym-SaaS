<li>
  <button type="button" class="flex items-center w-full p-2 text-base text-gray-900 transition duration-75 rounded-lg group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700" aria-controls="dropdown-{{Str::slug($attributes->get('label'))}}" data-collapse-toggle="dropdown-{{Str::slug($attributes->get('label'))}}">
    @if(isset($icon))
      {!! $icon !!}
    @endif
    <span class="flex-1 ms-3 text-left rtl:text-right whitespace-nowrap">{{$attributes->get('label')}}</span>
    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
      <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
    </svg>
  </button>
  <ul id="dropdown-{{Str::slug($attributes->get('label'))}}" class="hidden py-2 space-y-2">
    {{$slot}}
  </ul>
</li>
