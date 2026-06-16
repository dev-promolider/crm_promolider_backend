<!-- Incluyendo el layouts (Importante) -->
@extends('layouts.contentLayoutMaster')

<!-- Establecer el titulo del modulo (Importante) -->
@section('title', 'Opciones Generales')

@section('vendor-style')
    <!-- vendor css files -->
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/extensions/toastr.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap4.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap4.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/buttons.bootstrap4.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/rowGroup.bootstrap4.min.css')) }}">
@endsection

@section('page-style')
    <!-- Page css files -->
    <link rel="stylesheet" href="{{ asset(mix('css/base/plugins/extensions/ext-component-toastr.css')) }}">
    <!-- Icon Categories -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
@endsection

<!-- Codgio HTML del contenido (Importante) -->
@section('content')
    @can('is-admin')
        @foreach ($options as $option)
        @endforeach
        <config-option 
            :default_avatar="'{{ $options[0]->value }}'" 
            :daily_question="'{{ $options[1]->value }}'"
            :achievement="'{{ $options[2]->value }}'" 
            :badges_level_one="'{{ $options[3]->value }}'"
            :badges_level_two="'{{ $options[4]->value }}'" 
            :badges_level_three="'{{ $options[5]->value }}'"
            :money_points="'{{ $options[6]->value }}'" 
            :iva_rate="'{{ $options[9]->value }}'"
            :referral_commission="'{{ $options[10]->value }}'">
            <!-- Nueva prop agregada -->
        </config-option>
        <!-- Modificar segun el orden de la tabla de la BD -->
        {{-- <config-option :default_avatar="'{{ $options[1]->value }}'" :daily_question="'{{ $options[2]->value }}'"
            :achievement="'{{ $options[3]->value }}'" :badges_level_one="'{{ $options[4]->value }}'"
            :badges_level_two="'{{ $options[5]->value }}'" :badges_level_three="'{{ $options[6]->value }}'"
            :money_points="'{{ $options[7]->value }}'" :iva_rate="'{{ $options[0]->value }}'">
        </config-option> --}}
    @endcan
@endsection

@section('vendor-script')
    <!-- vendor files -->
    <script src="{{ asset(mix('vendors/js/extensions/toastr.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/jquery.dataTables.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/datatables.bootstrap4.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.responsive.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/responsive.bootstrap4.js')) }}"></script>
@endsection

@section('page-script')
    <!-- Page js files -->
    <script src="{{ asset(mix('js/scripts/extensions/ext-component-toastr.js')) }}"></script>
@endsection
