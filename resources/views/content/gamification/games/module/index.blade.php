@extends('layouts/contentLayoutMaster')
@section('title', "Gamificación de Módulos")
@section('vendor-style')
<link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap4.min.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap4.min.css')) }}">
@endsection
@section('content')
<div class="d-flex flex-row-reverse">
    <a href="{{ route('game-create', $course->id) }}" class="btn btn-link">Regresar</a>
</div>
<module-gamification :course='@json($course)' :game_type='@json($game_type)' :modules='@json($modules)'></module-gamification>
@endsection
@section('vendor-script')
<script src="{{ asset(mix('vendors/js/tables/datatable/jquery.dataTables.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/datatables.bootstrap4.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.responsive.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/responsive.bootstrap4.js')) }}"></script>
@endsection