@extends('layouts/contentLayoutMaster')
@section('title', "Crear Examen")
@section('vendor-style')
<link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap4.min.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap4.min.css')) }}">
@endsection
@section('content')
<div class="d-flex flex-row-reverse">
    <a href="{{ route('exam-module-create', $course->id) }}" class="btn btn-link">Crear examen de módulo</a>
    <a href="{{ route('exam-lesson-create', $course->id) }}" class="btn btn-link">Crear examen de clase</a>
    <a href="{{ route('courses.index') }}" class="btn btn-link">Volver a Cursos</a>
</div>
<course-create-exam :course='@json($course)'></course-create-exam>
@endsection
@section('vendor-script')
<script src="{{ asset(mix('vendors/js/tables/datatable/jquery.dataTables.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/datatables.bootstrap4.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.responsive.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/responsive.bootstrap4.js')) }}"></script>
@endsection