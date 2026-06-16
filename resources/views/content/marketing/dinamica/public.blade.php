@php
  $pageConfigs = [
    'showMenu' => false,
    'blankPage' => true,
    'layoutWidth' => 'full',
  ];
@endphp

@extends('layouts.fullLayoutMaster')

@section('title', $dinamica->nombre ?? 'Dinámica')

@section('conn-ws', '1')

@section('content')
<div id="app">
  <ruleta-public-main
    :dinamica='@json($dinamica)'
    :hay-ganador='@json($hayGanador)'
    :registros='@json($registros->items())'
    :turno-actual='@json($turnoActual)'
    :usuario-registro='@json($usuarioRegistro)'
    :es-mi-turno='@json($esMiTurno)'
    :registrar-ganador-url="'{{ route('dinamica.public.registrarGanador', $dinamica->slug) }}'"
    :marcar-jugado-url="'{{ route('dinamica.public.marcarJugado', $dinamica->slug) }}'"
    :spin-url="'{{ route('dinamica.public.spin', $dinamica->slug) }}'"
    :tiempo-restante='@json($tiempoRestante ?? null)'
    :turno-started-at="'{{ $turno_started_at ?? '' }}'"
    :turno-expires-at="'{{ $turno_expires_at ?? '' }}'"
    :turno-duration-seconds='@json($turno_duration_seconds ?? 90)'
    :register-url="'{{ route('dinamica.public.register', $dinamica->slug) }}'"
    :registration-seconds-left='@json($registration_seconds_left)'
    :registration-window-minutes='@json($registration_window_minutes)'
    :registration-limit-enabled='@json($registration_limit_enabled)'
    :participants-feed-url="'{{ route('dinamica.public.participants', $dinamica->slug) }}'"
    :status-url="'{{ route('dinamica.public.status', $dinamica->slug) }}'"
    :show-register-panel='@json((!$usuarioRegistro && !$hayGanador && $registration_is_open && !$limiteParticipantesAlcanzado))'
  />
</div>
@endsection
