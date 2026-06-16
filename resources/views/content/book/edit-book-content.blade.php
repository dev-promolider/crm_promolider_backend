@extends('layouts/contentLayoutMaster')
@section('title', 'Contenido del Libro')
@section('content')
<edit-book-content :course="{{ $course }}" :observations='@json($observations)'></edit-book-content>
@endsection