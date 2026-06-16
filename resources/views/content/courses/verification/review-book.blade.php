@extends('layouts/contentLayoutMaster')
@section('title', 'Revisión Libro')
@section('content')
<review-book :course='@json($course)'></review-book>
@endsection