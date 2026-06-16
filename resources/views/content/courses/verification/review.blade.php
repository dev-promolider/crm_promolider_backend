@extends('layouts/contentLayoutMaster')
@section('title', 'Revisión Curso')
@section('content')
<review-course :course='@json($course)' :ready='@json($ready)'></review-course>
@endsection