<table id="table" {{$attributes->merge(['class'=>'w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400'])}}>
  {{$slot}}
</table>
@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/simple-datatables@9.0.3"></script>
  <script>
  if (document.getElementById("table") && typeof simpleDatatables.DataTable !== 'undefined') {
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
