@extends('layouts/contentLayoutMaster')
@section('title', "Modulos | $course->title")
@section('content')
<div class="d-flex flex-row-reverse">
    <a href="{{ route('courses.index') }}" class="btn btn-link">Volver a Cursos</a>
    <a href="/course/exam/{{$course->id}}/create" class="btn btn-link">Crear examen</a>
    <a href="{{ route('exam-module-create', $course->id) }}" class="btn btn-link">Crear examen de módulo</a>
    <a href="{{ route('exam-lesson-create', $course->id) }}" class="btn btn-link">Crear examen de clase</a>
</div>
<course-module-list :modules='@json($modules)' :courses='@json($course)'></course-module-list>
@endsection