@extends('layouts.contentLayoutMaster')

@section('title', 'Especificaciones de la Dinámica')

@section('content')
    <div class="d-flex flex-row-reverse mb-3">
        <a href="{{ route('marketing.tools') }}" class="btn btn-link">
            <i class="feather icon-arrow-left"></i> {{ __('locale.Back to') }} {{ __('locale.tools') }}
        </a>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <dinamica-specifications-form 
                    tools-url="{{ route('marketing.tools') }}"
                    store-url="{{ route('marketing.dinamica.store') }}"
                    :dinamica-data="{{ $dinamica ? json_encode(['id' => $dinamica->id, 'nombre' => $dinamica->nombre, 'descripcion' => $dinamica->descripcion, 'modo_inscripcion' => $dinamica->modo_inscripcion, 'tiempo_inscripcion' => $dinamica->tiempo_inscripcion, 'max_participantes' => $dinamica->max_participantes, 'mostrar_inscritos' => $dinamica->mostrar_inscritos, 'tipo_premio' => $dinamica->tipo_premio, 'max_ganadores' => $dinamica->max_ganadores]) : 'null' }}"
                    :premios-data="{{ json_encode($premios) }}"
                ></dinamica-specifications-form>
            </div>
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
