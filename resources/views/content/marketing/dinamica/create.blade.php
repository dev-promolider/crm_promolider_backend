@extends('layouts.contentLayoutMaster')

@section('title', 'Crear Dinámica')

@section('content')
    <div id="app">
        <div class="d-flex flex-row-reverse">
            <a href="{{ route('marketing.tools') }}" class="btn btn-link">{{ __('locale.Back to') }} {{ __('locale.tools') }}</a>
        </div>
        <div class="alert alert-info mb-2">
            Esta sección será utilizada para configurar dinámicas (como la ruleta) en detalle. Por ahora solo muestra la estructura básica; más lógica se agregará después.
        </div>
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Crear Nueva Dinámica</h4>
            </div>
            <div class="card-body">
                <dinamica-form 
                    :categories="{{ json_encode($categories) }}"
                    specifications-url="{{ route('marketing.dinamica.specifications') }}"
                    trivia-url="{{ route('marketing.dinamica.trivia') }}"
                ></dinamica-form>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Últimas dinámicas</h4>
                <small class="text-muted">Listado paginado (10 por página)</small>
            </div>
            <div class="card-body p-0">
                @if($dinamicas->count() === 0)
                    <div class="p-3 text-muted">Aún no has creado dinámicas.</div>
                @else
                    <div class="table-responsive" style="overflow: visible;">
                        <table class="table mb-0 table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Tipo</th>
                                    <th>Premio</th>
                                    <th>Activación</th>
                                    <th class="text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dinamicas as $dinamica)
                                    <tr>
                                        <td>{{ $dinamica->nombre }}</td>
                                        <td>
                                            @php
                                                $tipo = $dinamica->tipo_dinamica ?? 'ruleta';
                                                $tipoBadge = $tipo === 'trivia' ? 'badge-info' : 'badge-secondary';
                                                $editUrl = $tipo === 'trivia'
                                                    ? route('marketing.dinamica.trivia.edit', $dinamica->id)
                                                    : route('marketing.dinamica.specifications', ['edit' => $dinamica->id]);
                                            @endphp
                                            <span class="badge badge-pill {{ $tipoBadge }} text-uppercase">{{ ucfirst($tipo) }}</span>
                                        </td>
                                        <td>{{ $dinamica->tipo_premio }}</td>
                                        <td>
                                            @php
                                                $hayGanador = \App\Models\DinamicaRegistro::where('dinamica_id', $dinamica->id)
                                                    ->where('ha_ganado', true)
                                                    ->exists();
                                            @endphp
                                            @if($hayGanador)
                                                <span class="badge badge-primary">Finalizada</span>
                                            @elseif($dinamica->is_active)
                                                <span class="badge badge-success">Activa</span>
                                            @else
                                                <span class="badge badge-warning">Pendiente</span>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            <div class="btn-group dropdown" data-display="static">
                                                <button type="button" class="btn btn-sm btn-light dropdown-toggle font-weight-bold border-0" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="Más opciones" title="Opciones">...</button>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <a class="dropdown-item" href="{{ $editUrl }}"><i class="feather icon-edit"></i> Editar</a>
                                                    @if(!empty($dinamica->slug))
                                                        <a class="dropdown-item" href="{{ route('dinamica.public', $dinamica->slug) }}" target="_blank"><i class="feather icon-eye"></i> Ver enlace público</a>
                                                        <a class="dropdown-item" href="#" onclick="copiarEnlacePublico(event, '{{ route('dinamica.public', $dinamica->slug) }}')"><i class="feather icon-share-2"></i> Compartir enlace</a>
                                                        <div class="dropdown-divider"></div>
                                                    @else
                                                        <span class="dropdown-item text-muted"><i class="feather icon-link-2"></i> Sin enlace</span>
                                                        <div class="dropdown-divider"></div>
                                                    @endif
                                                    <form action="{{ route('marketing.dinamica.toggle', $dinamica->id) }}" method="POST" class="px-3 py-1">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-link p-0 text-left w-100"><i class="feather icon-power"></i> {{ $dinamica->is_active ? 'Desactivar' : 'Activar' }}</button>
                                                    </form>
                                                    <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item text-danger" href="#" onclick="eliminarDinamica(event, {{ $dinamica->id }}, '{{ $dinamica->nombre }}');"><i class="feather icon-trash-2"></i> Eliminar</a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($dinamicas instanceof \Illuminate\Pagination\LengthAwarePaginator)
                        <div class="px-3 pb-2 pt-2">
                            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                                <small class="text-muted mb-2 mb-md-0">Mostrando {{ $dinamicas->firstItem() }}-{{ $dinamicas->lastItem() }} de {{ $dinamicas->total() }}</small>
                                <div>
                                    {{ $dinamicas->links('pagination::bootstrap-4') }}
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
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

// Función para copiar enlace público desde el dropdown
function copiarEnlacePublico(event, url) {
    event.preventDefault();
    
    // Crear input temporal
    const input = document.createElement('input');
    input.value = url;
    document.body.appendChild(input);
    input.select();
    input.setSelectionRange(0, 99999);
    
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(function(){
            alert('Enlace copiado al portapapeles');
        }).catch(function(){
            document.execCommand('copy');
            alert('Enlace copiado al portapapeles');
        });
    } else {
        document.execCommand('copy');
        alert('Enlace copiado al portapapeles');
    }
    
    document.body.removeChild(input);
}

// Función para eliminar dinámica
function eliminarDinamica(event, id, nombre) {
    event.preventDefault();
    
    if (!confirm('¿Estás seguro de eliminar la dinámica "' + nombre + '"? Esta acción no se puede deshacer.')) {
        return;
    }
    
    fetch('/marketing/dinamica/' + id, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message || 'Error al eliminar la dinámica');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al eliminar la dinámica');
    });
}
</script>
@endsection
