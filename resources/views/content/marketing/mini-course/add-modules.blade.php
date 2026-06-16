@extends('layouts.contentLayoutMaster')

@section('title', 'Agregar Módulos - ' .$miniCourse->title)

@section('content')
<div class="container-fluid">
    <mini-course-modules-form :mini-course="{{ json_encode($miniCourse) }}"></mini-course-modules-form>
</div>
@endsection

@section('page-script')
<script>
// Verificar que el token CSRF esté disponible
if (!document.querySelector('meta[name="csrf-token"]')) {
    const meta = document.createElement('meta');
    meta.name = 'csrf-token';
    meta.content = '{{ csrf_token() }}';
    document.head.appendChild(meta);
}
</script>
@endsection