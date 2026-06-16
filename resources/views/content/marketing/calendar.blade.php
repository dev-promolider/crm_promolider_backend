@extends('layouts.contentLayoutMaster') {{-- Tu layout base --}}

@section('title', 'Calendario')

@section('content')
    <div class="card">
        <div id="app">
            <calendar-component></calendar-component>
        </div>
    </div>
@endsection
