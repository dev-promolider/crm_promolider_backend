@extends('layouts.contentLayoutMaster')

@section('title', 'Crear Curso Gratuito')

@section('content')
    <div id="app">
        <freecourse-create :categories-list='@json($categories)'></freecourse-create> <!-- Aquí se carga el componente Vue -->
    </div>
@endsection

@section('vendor-script')
    <script src="{{ mix('js/app.js') }}"></script> <!-- Asegúrate de tener el archivo JS correcto -->
@endsection
