@extends('layouts.fullLayoutMaster')

@section('title', 'Resultados de la Trivia')

@push('styles')
<style>
body,
.app-content,
.content-wrapper,
.content-body {
    background: #05060a !important;
    min-height: 100vh;
}
.app-content {
    padding: 0 !important;
    margin-left: 0 !important;
    width: 100vw;
}
.content-wrapper {
    margin: 0 !important;
    padding: 0 !important;
    width: 100%;
    max-width: none;
}
.content-body {
    padding: 0 !important;
}
</style>
@endpush

@section('content')
<trivia-resultados
    :dinamica='@json($dinamica ?? [])'
    :categoria='@json($categoria ?? "Cultura General")'
    :puntaje-total='@json($puntajeTotal ?? 0)'
    :puntajes='@json($puntajes ?? [])'
    :leaderboard='@json($leaderboard ?? [])'
    :winner='@json($winner ?? null)'
    :total-participantes='@json($totalParticipantes ?? 0)'
    :casillas-totales='@json($casillasTotales ?? 0)'
    :blocks='@json($blocks ?? [])'
    :slug='@json($slug ?? null)'
/>
@endsection
