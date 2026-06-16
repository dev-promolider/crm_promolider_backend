@extends('layouts.contentLayoutMaster')

@section('title', 'Crear Link de Pago')

@section('content')
<div class="container">
    <h2>Crear Link de Pago</h2>

    <form method="POST" action="{{ route('payment-links.store') }}">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Nombre del Link</label>
                    <input type="text" name="name" class="form-control" required 
                           placeholder="Ej: Pago por servicios de consultoría">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Monto (USD)</label>
                    <input type="number" name="amount" step="0.01" min="1" class="form-control" required 
                           placeholder="100.00">
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>Descripción</label>
            <textarea name="description" class="form-control" rows="3" required 
                      placeholder="Descripción del producto o servicio"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Crear Link</button>
        <a href="{{ route('payment-links.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection