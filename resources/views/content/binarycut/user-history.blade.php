@extends('layouts.contentLayoutMaster')
@section('title', 'Historial de Corte Binario del Usuario')

@section('content')
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Historial de Corte Binario - {{ $histories->first()->user->fullName ?? 'Usuario' }}</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th>Rango</th>
                            <th>Puntos Izquierda</th>
                            <th>Puntos Derecha</th>
                            <th>Monto Transferido</th>
                            <th>Fecha de Corte</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($histories as $history)
                            <tr>
                                <td>{{ $history->rank->name }}</td>
                                <td>{{ number_format($history->left_points, 2) }}</td>
                                <td>{{ number_format($history->right_points, 2) }}</td>
                                <td>${{ number_format($history->transferred_amount, 2) }}</td>
                                <td>{{ $history->created_at->format('d/m/Y H:i:s') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No hay registros disponibles</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{ $histories->links() }}
            </div>
        </div>
    </div>
@endsection
