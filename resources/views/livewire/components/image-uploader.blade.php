<div class="space-y-4" x-data="{ draggedIndex: null }">
  @if($errors->any())
    <div class="flux-alert flux-alert-danger">
      <ul class="list-disc list-inside">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif


  <div class="flux-dropzone border-2 border-dashed border-gray-300 rounded-lg" wire:loading.class="opacity-50">
    <input type="file" {{ $multiple ? 'multiple' : '' }} wire:model="images" class="hidden" id="uploadInput">
    <label for="uploadInput" class="flux-card p-6 cursor-pointer text-center">
      <div class="text-gray-600">{{ __('Click here or drag files here') }}</div>
    </label>
  </div>


    @if ($previews)
      @php
        $count = count($previews);

        // Hvis multiple = false → kun 1 kolonne
        // Hvis multiple = true → 1–4 kolonner afhængig af antal billeder
        $cols = !$multiple ? 1 : min($count, 4);

        $gridClass = "grid grid-cols-{$cols} gap-4";
      @endphp

      <div class="{{ $gridClass }}" @dragover.prevent @drop.prevent="">
        @foreach ($previews as $index => $preview)
          <div class="relative flux-card overflow-hidden"
               x-data
               draggable="true"
               @dragstart="draggedIndex = {{ $index }}"
               @dragover.prevent
               @drop="
                let temp = $wire.previews[draggedIndex];
                $wire.previews.splice(draggedIndex, 1);
                $wire.previews.splice({{ $index }}, 0, temp);
                draggedIndex = null;
            "
          >

            <img src="{{ $preview }}" class="rounded w-full">

            <button wire:click="removePreview({{ $index }})"
                    class="absolute top-2 right-2 bg-red-600 text-white px-2 py-1 rounded">
              X
            </button>

            @if(isset($progress[$index]))
              <div class="absolute bottom-0 left-0 w-full bg-gray-200 h-2">
                <div class="bg-blue-600 h-2"
                     style="width: {{ $progress[$index] }}%"></div>
              </div>
            @endif

          </div>
        @endforeach
      </div>
    @endif

</div>
