@extends('layouts.contentLayoutMaster')

@section('title', 'Links de Pago')
@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Links de Pago</h2>
        <a href="{{ route('marketing.payments-link.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Crear Link
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>Nombre</th>
                    <th>Monto</th>
                    <th>Descripción</th>
                    <th>Usos</th>
                    <th>Estado</th>
                    <th>Link</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($links as $link)
                <tr>
                    <td>
                        <strong>{{ $link->name }}</strong>
                        <br>
                        <small class="text-muted">{{ $link->created_at->format('d/m/Y H:i') }}</small>
                    </td>
                    <td>
                        <span class="h6 text-success">${{ number_format($link->amount, 2) }}</span>
                    </td>
                    <td>
                        <span class="text-truncate d-inline-block" style="max-width: 200px;" 
                              title="{{ $link->description }}">
                            {{ $link->description }}
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-info">{{ $link->usage_count }}</span>
                    </td>
                    <td>
                        <span class="badge {{ $link->active ? 'badge-success' : 'badge-danger' }}">
                            {{ $link->active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                    <td>
                        <div class="input-group input-group-sm" style="max-width: 300px;">
                            <input type="text" class="form-control form-control-sm" 
                                   value="{{ route('payment-links.show', $link->slug) }}" 
                                   readonly id="link-{{ $link->id }}">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary btn-sm" 
                                        onclick="copyToClipboard('link-{{ $link->id }}', this)">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="btn-group-vertical btn-group-sm">
                            <a href="{{ route('payment-links.show', $link->slug) }}" 
                               class="btn btn-outline-info btn-sm" target="_blank">
                                <i class="fas fa-eye"></i> Ver
                            </a>
                            
                            <form method="POST" action="{{ route('payment-links.toggle', $link) }}" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $link->active ? 'btn-warning' : 'btn-success' }}">
                                    <i class="fas {{ $link->active ? 'fa-pause' : 'fa-play' }}"></i>
                                    {{ $link->active ? 'Pausar' : 'Activar' }}
                                </button>
                            </form>
                            
                            <form method="POST" action="{{ route('payment-links.destroy', $link) }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" 
                                        onclick="return confirm('¿Eliminar este link de pago?')">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No hay links de pago creados</p>
                        <a href="{{ route('marketing.payments-link.create') }}" class="btn btn-primary">
                            Crear primer link
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $links->links() }}
</div>

<script>
function copyToClipboard(inputId, button) {
    const input = document.getElementById(inputId);
    input.select();
    input.setSelectionRange(0, 99999); // Para móviles
    
    navigator.clipboard.writeText(input.value).then(() => {
        // Cambiar texto del botón temporalmente
        const originalHTML = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check"></i>';
        button.classList.add('btn-success');
        button.classList.remove('btn-outline-secondary');
        
        setTimeout(() => {
            button.innerHTML = originalHTML;
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-secondary');
        }, 2000);
    }).catch(() => {
        alert('Error al copiar. Selecciona el texto manualmente.');
    });
}
</script>
@endsection