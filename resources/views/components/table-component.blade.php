<div class="table-responsive">
    <table class="table display {{$tableclass ?? ''}}" id="myTable">
      <thead class="thead-{{$typehead ?? '' }}">
        {{$theadRows }}
      </thead>
      <tbody>
        {{$tbodyRows }}
      </tbody>
    </table>
    {{$slot}}
  </div>
@if ($datatable)

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap4.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap4.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/buttons.bootstrap4.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/rowGroup.bootstrap4.min.css')) }}">
@endsection

@section('vendor-script')
  <script src="{{ asset(mix('vendors/js/tables/datatable/jquery.dataTables.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/datatables.bootstrap4.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.responsive.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/responsive.bootstrap4.js')) }}"></script>
@endsection

@section('page-script')
  <!-- <script>
    $(document).ready( function () {
        $('#myTable').DataTable( {
            responsive: true,
            processing: true,


            "language":{
                                "decimal":        "",
                                "emptyTable":     "No hay contenido en la tabla",
                                "info":           "Showing _START_ to _END_ of _TOTAL_ entries",
                                "infoEmpty":      "Mostrando 0 de 0 de 0 registros",
                                "infoFiltered":   "(filtered from _MAX_ total entries)",
                                "infoPostFix":    "",
                                "thousands":      ",",
                                "lengthMenu":     "Mostrar _MENU_ registros",
                                "loadingRecords": "Loading...",
                                "processing":     "",
                                "search":         "Buscar:",
                                "zeroRecords":    "No matching records found",
                                "paginate": {
                                    "first":      "First",
                                    "last":       "Last",
                                    "next":       "Siguiente",
                                    "previous":   "Anterior"
                                },
                                "aria": {
                                    "sortAscending":  ": activate to sort column ascending",
                                    "sortDescending": ": activate to sort column descending"
                                }
                            }

        });
     } );
 </script> -->
@endsection

@endif