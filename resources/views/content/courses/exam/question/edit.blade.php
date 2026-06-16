@extends('layouts/contentLayoutMaster')
@section('title', "Editar Pregunta")
@section('content')
<div class="d-flex flex-row-reverse">
    <div class="d-flex flex-row-reverse">

    @if($type == 1)
    <a class="btn btn-link" href="{{ route('exam-edit', $exam->id) }}">Regresar a Lista de Preguntas</a>
    @elseif($type == 2)
    <a class="btn btn-link" href="{{ route('exam-module-edit', $exam->id) }}">Regresar a Lista de Preguntas</a>
    @else($type == 3)
    <a class="btn btn-link" href="{{ route('exam-lesson-edit', $exam->id) }}">Regresar a Lista de Preguntas</a>
    @endif

    
    </div>
</div>
<course-exam-question-details :exam='@json($exam)' :question='@json($question)' :question_types='@json($question_types)'></course-exam-question-details>
@endsection