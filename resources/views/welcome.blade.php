<x-layouts.guest :title="__('Dashboard')">
  <div class="max-w-[1920px] mx-auto py-3 sm:px-6 lg:px-3 w-full">
    <div class=" w-full text-[13px] leading-[20px] flex-1 p-3 pb-12 lg:p-2 bg-white dark:bg-[#161615] dark:text-[#EDEDEC] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-es-lg rounded-ee-lg lg:rounded-ss-lg lg:rounded-ee-none">
      @livewire('components.gym-class-calendar')
    </div>
  </div>
</x-layouts.guest>
