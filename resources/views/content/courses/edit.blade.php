@extends('layouts/contentLayoutMaster')
@section('title', "$course->title")
@section('content')


<!-- <div class="mb-2">
    <a href="{{ route('module.create') }}" class="btn btn-primary">Añadir Módulo</a>
</div> -->

<course-add-module :modules-list='@json($modules)'></course-add-module>
<course-module-list :modules='@json($modules)' :course='@json($course)'></course-module-list>




<!-- <course-edit :course="{{$course}}" :categories-list='@json($categories)' :route="'route(courses.update)'"></course-edit> -->
{{route('courses.update')}}
@endsection