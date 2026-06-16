@extends('layouts/contentLayoutMaster')
@section('title', "Editar Examen")
@section('content')
<div class="d-flex flex-row-reverse">
    @if($type == 1)
    <a class="btn btn-link" href="{{ route('exam-create', $course_id) }}">Regresar a Lista de Examenes</a>
    @elseif($type == 2)
    <a class="btn btn-link" href="{{ route('exam-module-create', $course_id) }}">Regresar a Lista de Examenes</a>
    @else($type == 3)
    <a class="btn btn-link" href="{{ route('exam-lesson-create', $course_id) }}">Regresar a Lista de Examenes</a>
    @endif
</div>
<div class="row">
    <div class="col-md-12 col-lg-12">
        <course-edit-exam :exam='@json($exam)'></course-edit-exam>
    </div>
</div>
@endsection