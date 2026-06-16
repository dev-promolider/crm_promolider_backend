@extends('layouts.contentLayoutMaster')
@section('title', 'Corte Binario')

@section('content')
    @can('is-admin')
        <form action="{{ route('binarycut.store') }}" method="post">
            @csrf
            <input type="submit" value="¿Aplicar corte?" class="btn btn-danger">
        </form>

        @if (session('success'))
            <div role="alert" class="alert alert-success">
                <div class="alert-body">
                    <span> <strong>Success!</strong> {{ session('success') }} </span>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
        @endif

        <x-table-component tableclass="mt-1 table-hover table-striped table-bordered" :datatable=true>
            <x-slot name="theadRows">
                <tr>
                    <th>Usuario</th>
                    <th>Rango Actual</th>
                    <th>Puntos Izquierda</th>
                    <th>Puntos Derecha</th>
                    <th>Cantidad a transferir</th>
                    <th>Puntos Excedentes</th>
                    <th>Acciones</th>
                </tr>
            </x-slot>
            <x-slot name="tbodyRows">
                @foreach ($users as $user)
                    @php
                        $currentRank = App\Models\RankBinary::where('user_id', $user->id)
                            ->orderBy('batch', 'desc')
                            ->first();

                        if (!$currentRank) {
                            $rankInfo = App\Models\RankBonus::where('id', 1)->first();
                        } else {
                            $rankInfo = App\Models\RankBonus::find($currentRank->rank_id);
                        }

                        // Calcular puntos a transferir y excedentes
                        $leftPoints = $user->LeftPoints;
                        $rightPoints = $user->RightPoints;
                        $minPoints = min($leftPoints, $rightPoints);
                        $transferRate = $user->accountType->pay_in_binary / 100;
                        $maxTransfer = $rankInfo->max_pay;

                        $amountToTransfer = $minPoints * $transferRate;
                        $exceedingPoints = max(0, $minPoints * $transferRate - $maxTransfer);
                    @endphp
                    <tr>
                        <td> {{ $user->fullName }} </td>
                        <td>{{ $rankInfo->name }}</td>
                        <td> {{ $user->LeftPoints }} </td>
                        <td> {{ $user->RightPoints }} </td>
                        <td>{{ min($amountToTransfer, $maxTransfer) }}</td>
                        <td>{{ $exceedingPoints }}</td>
                        <td>
                            <a href="{{ route('binarycut.user-history', $user->id) }}" class="btn btn-sm btn-success">
                                Ver Historial
                            </a>
                        </td>
                    </tr>
                @endforeach
            </x-slot>
        </x-table-component>
    @endcan
@endsection
