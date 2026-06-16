@extends('layouts.contentLayoutMaster')
@section('title', 'Canje de Premios')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/extensions/toastr.min.css')) }}">
@endsection

@section('content')
    <rewards-redemption user-id="{{ auth()->user()->id }}"></rewards-redemption>
@endsection

@section('vendor-script')
    <script src="{{ asset(mix('vendors/js/extensions/toastr.min.js')) }}"></script>
@endsection