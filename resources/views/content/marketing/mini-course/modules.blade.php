@extends('layouts.contentLayoutMaster')
@section('title', 'Módulos | ' .$miniCourse->title)
@section('content')
<div class="d-flex flex-row-reverse">
    <a href="{{ route('marketing.tools') }}" class="btn btn-link">Volver a Herramientas</a>
</div>
<div class="container-fluid">
    <mini-courses-summary :mini-course-id="{{ $miniCourse->id }}"></mini-courses-summary>
</div>
@endsection