<div class="space-y-6">
  <div class="flex justify-between items-center mb-4">
    <div class="flex justify-self-start">
      <h1 class="text-2xl font-semibold">{{ __('Subscriptions') }}</h1>
    </div>
    <div class="p-4 flex w-full justify-end items-center">
      <div class="flex items-center gap-2 justify-self-end">
        <x-flowbite.button class="hover:cursor-pointer" wire:click="export" variant="ghost">
          {{ __('Export') }}
        </x-flowbite.button>
{{--        <x-flowbite.link href="{{ route('classes.create') }}" variant="ghost">--}}
{{--          {{ __('New Class') }}--}}
{{--        </x-flowbite.button>--}}
      </div>
    </div>
  </div>

  @if (session('status'))
    <div class="rounded-md bg-green-50 p-3 text-green-700">{{ __(session('status')) }}</div>
  @endif

  <div class="relative overflow-x-auto ">

    <x-flowbite.table>
      <x-flowbite.table.head class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
        <x-flowbite.table.head.row>
          <x-flowbite.table.head.sortable field="id" :$sortField :$sortDirection>{{ __('ID') }}</x-flowbite.table.head.sortable>
          <x-flowbite.table.head.cell>{{ __('User') }}</x-flowbite.table.head.cell>
          <x-flowbite.table.head.cell>{{ __('Plan') }}</x-flowbite.table.head.cell>
          <x-flowbite.table.head.sortable field="stripe_subscription_id" :$sortField :$sortDirection>{{ __('Stripe Subscription ID') }}</x-flowbite.table.head.sortable>
          <x-flowbite.table.head.sortable field="stripe_price_id" :$sortField :$sortDirection>{{ __('Stripe Price ID') }}</x-flowbite.table.head.sortable>
          <x-flowbite.table.head.sortable field="status" :$sortField :$sortDirection>{{ __('Status') }}</x-flowbite.table.head.sortable>
          <x-flowbite.table.head.sortable field="current_period_end" :$sortField :$sortDirection>{{ __('Current Period End') }}</x-flowbite.table.head.sortable>
          <x-flowbite.table.head.cell class="text-right">{{ __('Actions') }}</x-flowbite.table.head.cell>
        </x-flowbite.table.head.row>
      </x-flowbite.table.head>

      <x-flowbite.table.body>
        @forelse ($subscriptions as $sub)
          <x-flowbite.table.body.row>
            <x-flowbite.table.body.cell>{{ $sub->id }}</x-flowbite.table.body.cell>
            <x-flowbite.table.body.cell>{{ $sub->user?->name }}</x-flowbite.table.body.cell>
            <x-flowbite.table.body.cell>
              @if($sub->plan)
                <a href="{{ route('plans.show', $sub->plan) }}" class="hover:underline">{{ $sub->plan->name }}</a>
              @else
                <span class="text-gray-400">{{ __('No Plan') }}</span>
              @endif
            </x-flowbite.table.body.cell>
            <x-flowbite.table.body.cell class="font-mono text-xs">{{ $sub->stripe_subscription_id }}</x-flowbite.table.body.cell>
            <x-flowbite.table.body.cell class="font-mono text-xs">{{ $sub->stripe_price_id }}</x-flowbite.table.body.cell>
            <x-flowbite.table.body.cell>{{ $sub->status }}</x-flowbite.table.body.cell>
            <x-flowbite.table.body.cell>{{ optional($sub->current_period_end)->format('Y-m-d H:i') }}</x-flowbite.table.body.cell>
            <x-flowbite.table.body.cell class="text-right space-x-2">
              {{-- Reserved for future actions (view/edit/delete) --}}
            </x-flowbite.table.body.cell>
          </x-flowbite.table.body.row>
        @empty
          <x-flowbite.table.body.row class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 ">
            <x-flowbite.table.body.cell class="w-4 p-4" colspan="7">{{ __('No subscriptions found.') }}</x-flowbite.table.body.cell>
          </x-flowbite.table.body.row>
        @endforelse
      </x-flowbite.table.body>
    </x-flowbite.table>
  </div>

  <div class="mt-4">
    {{ $subscriptions->links() }}
  </div>
</div>
