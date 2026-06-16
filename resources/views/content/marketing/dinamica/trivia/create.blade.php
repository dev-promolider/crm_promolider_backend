@extends('layouts.contentLayoutMaster')

@section('title', 'Crear trivia')

@section('content')
    <div id="app">
        @php
            $questionCategories = [
                ['id' => 1, 'name' => 'Bienestar integral'],
                ['id' => 2, 'name' => 'Cultura corporativa'],
                ['id' => 3, 'name' => 'Innovacion y tecnologia'],
                ['id' => 4, 'name' => 'Productos estrella'],
            ];
        @endphp

        @include('content.marketing.dinamica.trivia.partials.shell', [
            'active' => 'create',
            'breadcrumbs' => [
                ['label' => 'Marketing', 'url' => route('marketing.tools')],
                ['label' => 'Dinámica', 'url' => route('marketing.dinamica.create')],
                ['label' => 'Trivia', 'url' => route('marketing.dinamica.trivia')],
                ['label' => 'Crear', 'url' => null],
            ],
        ])

        <div class="card shadow-sm border-0">
            <div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                <div>
                    <h4 class="card-title mb-0">Crear nueva trivia</h4>
                    <small class="text-muted">Solo maqueta de UI · sin logica</small>
                </div>
                <span class="badge badge-light mt-2 mt-md-0">Marketing · Dinamicas</span>
            </div>
            <div class="card-body">
                <trivia-form-builder
                    mode="create"
                    :question-categories='@json($questionCategories)'
                ></trivia-form-builder>
            </div>
        </div>
    </div>
@endsection
