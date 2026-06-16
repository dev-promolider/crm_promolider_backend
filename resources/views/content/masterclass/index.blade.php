@extends('layouts/contentLayoutMaster')
@section('title', 'Masterclass')
@section('vendor-style')
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap4.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap4.min.css')) }}">
@endsection
@section('content')
    @if($permission)
        <div class="mb-3">
            <a href="{{ route('masterclass.create') }}" class="btn btn-primary">{{ __('locale.create_masterclass') }}</a>
        </div>
        <masterclass-table :user='@json($user)'></masterclass-table>
    @else
        <masterclass-request></masterclass-request>
    @endif
@endsection
@section('vendor-script')
    <script src="{{ asset(mix('vendors/js/tables/datatable/jquery.dataTables.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/datatables.bootstrap4.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.responsive.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/responsive.bootstrap4.js')) }}"></script>
@endsection