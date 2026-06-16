@extends('layouts/contentLayoutMaster')
@section('title', "Editar $game->title")
@section('content')
    <div class="d-flex flex-row-reverse">
        @if ($type == 1)
            <a href="{{ route('game-create', $course_id) }}" class="btn btn-link">Regresar a la Lista de dinámicas</a>
        @elseif($type == 2)
            <a href="{{ route('course.game.module', $course_id) }}" class="btn btn-link">Regresar a la Lista de dinámicas</a>
        @else($type == 3)
            <a href="{{ route('course.game.lesson', $course_id) }}" class="btn btn-link">Regresar a la Lista de dinámicas</a>
        @endif
    </div>
    <div class="row">
        <div class="col-md-12 col-lg-12">
            <course-edit-game-4 :game='@json($game)'
                :detail='@json($detail)'></course-edit-game-4>
        </div>
    </div>
@endsection
