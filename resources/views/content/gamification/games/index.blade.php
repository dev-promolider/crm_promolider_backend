@extends('layouts/contentLayoutMaster')
@section('title', "Dinámicas del Curso | $course->title")
@section('vendor-style')
<link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap4.min.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap4.min.css')) }}">
@endsection
@section('content')
<div class="d-flex flex-row-reverse">
    <a href="{{ route('course.game.module', $course->id) }}" class="btn btn-link">Crear dinámica de módulo</a>
    <a href="{{ route('course.game.lesson', $course->id) }}" class="btn btn-link">Crear dinámica de clase</a>
    <a href="{{ route('courses.index') }}" class="btn btn-link">Volver a Cursos</a>
</div>

<course-gamification :course='@json($course)' :game_type='@json($game_type)'></course-gamification>
@endsection
@section('vendor-script')
<script src="{{ asset(mix('vendors/js/tables/datatable/jquery.dataTables.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/datatables.bootstrap4.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.responsive.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/responsive.bootstrap4.js')) }}"></script>
@endsection