@if (session('status'))
  <div class="rounded-md bg-green-50 p-3 text-green-700">{{ __(session('status')) }}</div>
@endif
@if (session('error'))
  <div class="rounded-md bg-red-400 p-3 text-gray-900">{!! __(session('error')) !!}</div>
@endif
@if (session('success'))
  <div class="rounded-md bg-red-50 p-3 text-green-700">{{ __(session('success')) }}</div>
@endif
