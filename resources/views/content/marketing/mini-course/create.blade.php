@extends('layouts.contentLayoutMaster')

@section('title', 'Crear Mini Curso')

@section('content')
    <div class="d-flex flex-row-reverse">
        <a href="{{ route('marketing.tools') }}" class="btn btn-link">{{ __('locale.Back to') }} {{ __('locale.tools') }}</a>
    </div>
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Información General</h4>
        </div>
        <div class="card-body">
            <mini-course-form :categorias="{{ json_encode($categories) }}"></mini-course-form>
        </div>
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