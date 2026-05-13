@props(['useDataTables' => false])
<table @if($useDataTables) id="table" @endif {{$attributes->merge(['class'=>'w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400'])}}>
  {{$slot}}
</table>
@if($useDataTables)
@pushonce('scripts')
  <script src="https://cdn.jsdelivr.net/npm/simple-datatables@9.0.3"></script>
@endpushonce
@push('scripts')
  <script>
  if (document.getElementById("table") && typeof simpleDatatables !== 'undefined') {
  const dataTable = new simpleDatatables.DataTable("#table", {
  searchable: false,
  sortable: false,
    paging: false, // enable or disable pagination
    perPage: 10, // set the number of rows per page
    perPageSelect: [5, 10, 20, 50,100], // set the number of rows per page options
    firstLast: true, // enable or disable the first and last buttons
    nextPrev: true, // enable or disable the next and previous buttons
  });
  }
  </script>
  @endpush
@endif
