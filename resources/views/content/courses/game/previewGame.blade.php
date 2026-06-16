@extends('layouts/contentLayoutMaster')
@section('title', 'Pre-visualización')
@section('content')

  <dynamic-class :game-data='@json($data)'></dynamic-class>

@endsection
@section('page-script')
<script></script>
@endsection
