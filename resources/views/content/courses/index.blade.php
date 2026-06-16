@extends('layouts/contentLayoutMaster')
@section('title', 'Infoproductos')
@section('vendor-style')
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap4.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap4.min.css')) }}">
@endsection
@section('content')
    @if ($permission)
        <div class="mb-3 d-flex flex-wrap" style="gap: 10px">
            <a href="{{ route('courses.create', ['product_type_id' => 1]) }}" class="btn btn-primary">{{ __('locale.create_course') }}</a>
            <a href="{{ route('courses.create', ['product_type_id' => 2]) }}" class="btn btn-primary">{{ __('locale.create_book') }}</a>
        </div>
        <courses-table :user='@json($user)'></courses-table>
    @else
        <course-request></course-request>
    @endif
@endsection
@section('vendor-script')
    <script src="{{ asset(mix('vendors/js/tables/datatable/jquery.dataTables.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/datatables.bootstrap4.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.responsive.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/responsive.bootstrap4.js')) }}"></script>
@endsection
