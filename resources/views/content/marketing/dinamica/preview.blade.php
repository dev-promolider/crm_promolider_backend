@extends('layouts.fullLayoutMaster')

@section('title', 'Preview Trivia')

@push('styles')
<style>
body,
.app-content,
.content-wrapper,
.content-body {
    background: #05060a !important;
}

.app-content {
    padding: 0 !important;
}

.content-wrapper {
    margin: 0 auto;
    padding: 0 0 3rem;
}
</style>
@endpush

@section('content')
@php
    $triviaProps = [
        'categoria' => $categoria ?? 'Cultura General',
        'cantidadCasillas' => (int) ($cantidadCasillas ?? 5),
        'estadoInteraccion' => $estadoInteraccion ?? 'espera',
        'slug' => request()->route('slug'),
        'userId' => $usuarioId ?? null,
        'resultadosUrl' => $resultadosUrl ?? null,
        'blocks' => $blocksProgress ?? [],
        'nextPlayableIndex' => $nextPlayableIndex ?? 0,
        'lastCompletedIndex' => $lastCompletedIndex ?? null,
        'totalScore' => $totalScore ?? 0,
        'initialAnsweredNumbers' => $answeredNumbers ?? [],
        'hasMultipleBlocks' => $hasMultipleBlocks ?? false,
        'puntajeMaximo' => $puntajeTotal ?? 0,
    ];
@endphp
<trivia-main v-bind='@json($triviaProps)' />
@endsection

{{-- Ya no se requiere trivia-custom.css, el CSS está en el componente Vue --}}

{{-- El resto de scripts y estilos personalizados pueden moverse a un archivo trivia-custom.css/js si es necesario --}}
